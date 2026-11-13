<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\TrainingSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrainingScheduleController extends Controller
{
    /**
     * Display a listing of training schedules.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $query = TrainingSchedule::with(['dutySchedule.assignments.user', 'creator'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        $schedules = $query->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();
        $coaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true)->get();

        return view('schedules.index', compact('schedules', 'coaches', 'month', 'year'));
    }

    /**
     * Store a newly created schedule with duty piket assignments.
     */
    public function store(Request $request): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'activity_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'shift_name' => ['nullable', 'string', 'max:255'],
            'duty_notes' => ['nullable', 'string', 'max:1000'],
            'coaches' => ['nullable', 'array'],
            'coaches.*' => ['exists:users,id'],
            'role_tasks' => ['nullable', 'array'],
            'materials' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated, $actor) {
            $schedule = TrainingSchedule::create([
                'activity_name' => $validated['activity_name'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'created_by' => $actor->id,
            ]);

            $dutySchedule = DutySchedule::create([
                'training_schedule_id' => $schedule->id,
                'shift_name' => $validated['shift_name'] ?? 'Piket Mengajar Utama',
                'notes' => $validated['duty_notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            if (! empty($validated['coaches'])) {
                foreach ($validated['coaches'] as $index => $coachId) {
                    DutyAssignment::create([
                        'duty_schedule_id' => $dutySchedule->id,
                        'user_id' => $coachId,
                        'role_task' => $validated['role_tasks'][$index] ?? 'Instruktur Pendamping',
                        'training_materials' => $validated['materials'][$index] ?? null,
                        'status' => 'assigned',
                    ]);
                }
            }

            ActivityLog::record(
                $actor->id,
                'CREATE_SCHEDULE',
                "{$actor->name} menambahkan jadwal latihan baru: {$schedule->activity_name} ({$schedule->date->format('d/m/Y')}) dan menetapkan petugas piket."
            );
        });

        return back()->with('success', 'Jadwal latihan dan penugasan piket asisten pelatih berhasil disimpan.');
    }

    /**
     * Delete schedule.
     */
    public function destroy(Request $request, TrainingSchedule $schedule): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $name = $schedule->activity_name;
        $schedule->delete();

        ActivityLog::record(
            $actor->id,
            'DELETE_SCHEDULE',
            "{$actor->name} menghapus jadwal latihan: {$name}."
        );

        return back()->with('success', 'Jadwal latihan berhasil dihapus.');
    }
}

