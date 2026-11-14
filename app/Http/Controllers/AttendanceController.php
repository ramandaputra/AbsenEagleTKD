<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use App\Models\TrainingSchedule;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $salaryService
    ) {}

    /**
     * Redirect the old attendance list to the relevant detail or dashboard.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->isAsistenPelatih()) {
            $latestAttendance = Attendance::where('user_id', $user->id)
                ->orderByDesc('date')
                ->orderByDesc('time')
                ->first();

            if ($latestAttendance) {
                return redirect()->route('attendances.show', $latestAttendance);
            }
        }

        return redirect()->route('dashboard');
    }

    /**
     * Display a single attendance record with its submitted photos.
     */
    public function show(Request $request, Attendance $attendance): View
    {
        $user = $request->user();

        if (! $user->isAdmin() && (int) $attendance->user_id !== (int) $user->id) {
            abort(404);
        }

        $attendance->load(['user.role', 'trainingSchedule', 'photos', 'dutyAssignment.dutySchedule']);

        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show check-in form.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');
        $todayDate = $now->format('Y-m-d');

        $existingToday = Attendance::with(['trainingSchedule', 'photos'])
            ->where('user_id', $user->id)
            ->where('date', $todayDate)
            ->latest()
            ->first();

        $todaySchedules = TrainingSchedule::with('dutySchedule.assignments')
            ->where('date', $todayDate)
            ->orderBy('start_time')
            ->get();

        // If no schedule today, get upcoming schedules
        if ($todaySchedules->isEmpty()) {
            $todaySchedules = TrainingSchedule::where('date', '>=', $todayDate)
                ->orderBy('date')
                ->take(5)
                ->get();
        }

        return view('attendances.create', compact('user', 'existingToday', 'todaySchedules', 'todayDate', 'now'));
    }

    /**
     * Store attendance: Direct save, auto timestamp, photo evidence, no GPS.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        $validated = $request->validate([
            'training_schedule_id' => ['nullable', 'exists:training_schedules,id'],
            'status' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'photo_base64' => ['nullable', 'string'],
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'training_schedule_id' => $validated['training_schedule_id'] ?? null,
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'session_rate' => $user->rate_per_session ?: 50000.00,
        ]);

        // Upload photo
        $uploadDir = public_path('uploads/attendances');
        if (! File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'att_'.date('Ymd_His').'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);

            AttendancePhoto::create([
                'attendance_id' => $attendance->id,
                'photo_path' => 'uploads/attendances/'.$filename,
                'file_name' => $filename,
                'file_size' => $file->getSize(),
            ]);
        } elseif ($request->filled('photo_base64')) {
            $imageData = $request->input('photo_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
            } else {
                $type = 'jpg';
            }
            $imageData = base64_decode($imageData);

            if ($imageData !== false) {
                $filename = 'att_cam_'.date('Ymd_His').'_'.Str::random(8).'.'.$type;
                file_put_contents($uploadDir.'/'.$filename, $imageData);

                AttendancePhoto::create([
                    'attendance_id' => $attendance->id,
                    'photo_path' => 'uploads/attendances/'.$filename,
                    'file_name' => $filename,
                    'file_size' => strlen($imageData),
                ]);
            }
        }

        ActivityLog::record(
            $user->id,
            'CHECK_IN',
            "Asisten Pelatih {$user->name} mencatat absensi {$attendance->status} ({$attendance->date->format('d/m/Y')} {$attendance->time})."
        );

        return redirect()->route('attendances.show', $attendance)
            ->with('success', "Absensi mandiri ({$attendance->status}) berhasil disimpan otomatis dengan bukti foto.");
    }

    /**
     * Admin/Superadmin update or correct attendance.
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $old = $attendance->status;
        $attendance->update($validated);

        ActivityLog::record(
            $actor->id,
            'UPDATE_ATTENDANCE',
            "{$actor->name} mengoreksi status absensi {$attendance->user->name} dari {$old} menjadi {$attendance->status}."
        );

        return back()->with('success', 'Data absensi berhasil dikoreksi.');
    }
}
