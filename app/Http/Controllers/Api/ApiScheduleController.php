<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\TrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiScheduleController extends Controller
{
    /**
     * Get list of training schedules with integrated duty assignments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TrainingSchedule::with([
            'dutySchedule.assignments.user.role',
            'creator',
        ]);

        // If requested to see only schedules assigned to this coach
        if ($request->boolean('my_duties') || $user->isAsistenPelatih()) {
            if ($request->boolean('my_duties')) {
                $query->whereHas('dutySchedule.assignments', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        } elseif ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('date', $request->input('month'))
                  ->whereYear('date', $request->input('year'));
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $schedules = $query->orderBy('date', 'asc')->orderBy('start_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    /**
     * Show single schedule detail.
     */
    public function show(TrainingSchedule $schedule): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'schedule' => $schedule->load(['dutySchedule.assignments.user.role', 'creator']),
            ],
        ]);
    }

    /**
     * Create training schedule with integrated duty schedule and assigned assistant coaches.
     */
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Hanya Admin/Pelatih dan Superadmin yang dapat membuat jadwal.'], 403);
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
            'assigned_coaches' => ['nullable', 'array'],
            'assigned_coaches.*.user_id' => ['required', 'exists:users,id'],
            'assigned_coaches.*.role_task' => ['nullable', 'string', 'max:255'],
            'assigned_coaches.*.task_role' => ['nullable', 'string', 'max:255'],
            'assigned_coaches.*.training_materials' => ['nullable', 'string', 'max:1000'],
            'assigned_coaches.*.training_material' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $actor) {
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

            if (! empty($validated['assigned_coaches'])) {
                foreach ($validated['assigned_coaches'] as $assignment) {
                    DutyAssignment::create([
                        'duty_schedule_id' => $dutySchedule->id,
                        'user_id' => $assignment['user_id'],
                        'role_task' => $assignment['role_task'] ?? $assignment['task_role'] ?? 'Instruktur Pendamping',
                        'training_materials' => $assignment['training_materials'] ?? $assignment['training_material'] ?? null,
                        'status' => 'assigned',
                    ]);
                }
            }

            ActivityLog::record(
                $actor->id,
                'CREATE_SCHEDULE',
                "{$actor->name} membuat jadwal latihan: {$schedule->activity_name} ({$schedule->date->format('d/m/Y')}) dan menetapkan tugas piket mengajar."
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal latihan dan piket mengajar berhasil dibuat.',
                'data' => [
                    'schedule' => $schedule->load('dutySchedule.assignments.user.role'),
                ],
            ], 201);
        });
    }

    /**
     * Update training schedule and duty assignments.
     */
    public function update(Request $request, TrainingSchedule $schedule): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'activity_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'location' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:scheduled,ongoing,completed,cancelled'],
            'shift_name' => ['nullable', 'string', 'max:255'],
            'duty_notes' => ['nullable', 'string', 'max:1000'],
            'assigned_coaches' => ['nullable', 'array'],
            'assigned_coaches.*.user_id' => ['required', 'exists:users,id'],
            'assigned_coaches.*.role_task' => ['nullable', 'string', 'max:255'],
            'assigned_coaches.*.training_materials' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($validated, $schedule, $actor) {
            $schedule->update([
                'activity_name' => $validated['activity_name'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? $schedule->status,
            ]);

            $dutySchedule = $schedule->dutySchedule()->firstOrCreate(
                ['training_schedule_id' => $schedule->id],
                ['shift_name' => $validated['shift_name'] ?? 'Piket Mengajar Utama', 'created_by' => $actor->id]
            );

            if (isset($validated['shift_name']) || isset($validated['duty_notes'])) {
                $dutySchedule->update([
                    'shift_name' => $validated['shift_name'] ?? $dutySchedule->shift_name,
                    'notes' => $validated['duty_notes'] ?? $dutySchedule->notes,
                ]);
            }

            if (isset($validated['assigned_coaches'])) {
                $dutySchedule->assignments()->delete();
                foreach ($validated['assigned_coaches'] as $assignment) {
                    DutyAssignment::create([
                        'duty_schedule_id' => $dutySchedule->id,
                        'user_id' => $assignment['user_id'],
                        'role_task' => $assignment['role_task'] ?? 'Instruktur Pendamping',
                        'training_materials' => $assignment['training_materials'] ?? null,
                        'status' => 'assigned',
                    ]);
                }
            }

            ActivityLog::record(
                $actor->id,
                'UPDATE_SCHEDULE',
                "{$actor->name} memperbarui jadwal latihan: {$schedule->activity_name}."
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal latihan dan piket mengajar berhasil diperbarui.',
                'data' => [
                    'schedule' => $schedule->load('dutySchedule.assignments.user.role'),
                ],
            ]);
        });
    }

    /**
     * Delete schedule.
     */
    public function destroy(Request $request, TrainingSchedule $schedule): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $name = $schedule->activity_name;
        $schedule->delete();

        ActivityLog::record(
            $actor->id,
            'DELETE_SCHEDULE',
            "{$actor->name} menghapus jadwal latihan: {$name}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal latihan berhasil dihapus.',
        ]);
    }

    /**
     * Get teaching duty assignments for the authenticated assistant coach.
     */
    public function myDutyAssignments(Request $request): JsonResponse
    {
        $user = $request->user();

        $assignments = DutyAssignment::with(['dutySchedule.trainingSchedule'])
            ->where('user_id', $user->id)
            ->whereHas('dutySchedule.trainingSchedule')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }
}
