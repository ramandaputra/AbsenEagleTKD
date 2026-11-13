<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ApiAttendanceController extends Controller
{
    /**
     * Get list of attendances with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Attendance::with(['user.role', 'trainingSchedule', 'photos', 'dutyAssignment']);

        // Role restriction: assistant coaches can only see their own attendance
        if ($user->isAsistenPelatih()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->input('month'))
                  ->whereYear('date', $request->input('year'));
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('time', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Mobile check-in: Direct save, auto server timestamp, photo evidence, no GPS.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();
        $todayDate = $now->format('Y-m-d');
        $scheduleId = $request->input('training_schedule_id');

        $alreadyCheckedIn = Attendance::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->when($scheduleId, fn ($q) => $q->where('training_schedule_id', $scheduleId), fn ($q) => $q->whereNull('training_schedule_id'))
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini.',
            ], 422);
        }

        $validated = $request->validate([
            'training_schedule_id' => ['nullable', 'exists:training_schedules,id'],
            'duty_assignment_id' => ['nullable', 'exists:duty_assignments,id'],
            'status' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:10240'], // Max 10MB
            'photo_base64' => ['nullable', 'string'],
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'training_schedule_id' => $validated['training_schedule_id'] ?? null,
            'duty_assignment_id' => $validated['duty_assignment_id'] ?? null,
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'session_rate' => $user->rate_per_session ?: 50000.00,
        ]);

        // Handle Photo Evidence via Storage
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'att_'.date('Ymd_His').'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/attendances', $filename, 'public');

            AttendancePhoto::create([
                'attendance_id' => $attendance->id,
                'photo_path' => 'storage/'.$path,
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
                Storage::disk('public')->put('uploads/attendances/'.$filename, $imageData);

                AttendancePhoto::create([
                    'attendance_id' => $attendance->id,
                    'photo_path' => 'storage/uploads/attendances/'.$filename,
                    'file_name' => $filename,
                    'file_size' => strlen($imageData),
                ]);
            }
        }

        ActivityLog::record(
            $user->id,
            'CHECK_IN',
            "Asisten Pelatih {$user->name} mencatat absensi {$attendance->status} pada tanggal {$attendance->date->format('d/m/Y')} pukul {$attendance->time} WIB."
        );

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil disimpan secara otomatis.',
            'data' => [
                'attendance' => $attendance->load(['trainingSchedule', 'photos', 'dutyAssignment']),
            ],
        ], 201);
    }

    /**
     * Show attendance detail.
     */
    public function show(Attendance $attendance): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'attendance' => $attendance->load(['user.role', 'trainingSchedule', 'photos', 'dutyAssignment.dutySchedule']),
            ],
        ]);
    }

    /**
     * Admin/Superadmin correction of attendance.
     */
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:Hadir,Izin,Sakit,Alpa'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'time' => ['nullable', 'string'],
        ]);

        $oldStatus = $attendance->status;
        if ($validated['status'] === 'Hadir' && (float) $attendance->session_rate <= 0) {
            $validated['session_rate'] = $attendance->user->rate_per_session ?: 50000.00;
        } elseif ($validated['status'] !== 'Hadir') {
            $validated['session_rate'] = 0.00;
        }

        $attendance->update($validated);

        ActivityLog::record(
            $actor->id,
            'UPDATE_ATTENDANCE',
            "{$actor->name} mengoreksi absensi {$attendance->user->name} dari {$oldStatus} menjadi {$attendance->status}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dikoreksi.',
            'data' => [
                'attendance' => $attendance->load(['user', 'trainingSchedule', 'photos']),
            ],
        ]);
    }
}
