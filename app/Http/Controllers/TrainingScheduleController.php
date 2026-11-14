<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DutySchedule;
use App\Models\TrainingSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
        $coachQuery = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
            ->where('is_active', true);
        $this->constrainCoachQueryForActor($coachQuery, $user);

        $coaches = $coachQuery->orderBy('name')->get();
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
            'date' => ['required', 'date'],
            'activity_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'coaches' => ['required', 'array', 'min:1'],
            'coaches.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $coachQuery = User::whereKey($validated['coaches'])
            ->whereHas('role', fn ($query) => $query->where('name', 'asisten_pelatih'))
            ->where('is_active', true);

        $this->constrainCoachQueryForActor($coachQuery, $actor);

        $coaches = $coachQuery->get();

        if ($coaches->count() !== count($validated['coaches'])) {
            return back()
                ->withErrors(['coaches' => 'Pilih asisten pelatih aktif yang tersedia untuk dojang Anda.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $actor, $coaches) {
            $schedule = TrainingSchedule::firstOrCreate(
                [
                    'activity_name' => $validated['activity_name'],
                    'date' => $validated['date'],
                ],
                [
                    'start_time' => '16:00:00',
                    'end_time' => '18:00:00',
                    'location' => $actor->dojang?->address ?: ($actor->dojang?->name ?? 'Dojang Eagle Pusat GOR Pajajaran'),
                    'description' => $validated['description'] ?? null,
                    'created_by' => $actor->id,
                ]
            );

            if (! $schedule->wasRecentlyCreated) {
                $schedule->update([
                    'description' => $validated['description'] ?? null,
                ]);
            }

            $dutySchedule = DutySchedule::firstOrCreate(
                ['training_schedule_id' => $schedule->id],
                [
                    'shift_name' => 'Piket Mengajar Utama',
                    'created_by' => $actor->id,
                ]
            );

            $coachIds = $coaches->modelKeys();
            $dutySchedule->assignments()->whereNotIn('user_id', $coachIds)->delete();

            foreach ($coaches as $coach) {
                $dutySchedule->assignments()->firstOrCreate(
                    ['user_id' => $coach->id],
                    [
                        'role_task' => 'Instruktur Pendamping',
                        'status' => 'assigned',
                    ]
                );
            }

            ActivityLog::record(
                $actor->id,
                'CREATE_SCHEDULE',
                "{$actor->name} menetapkan {$coaches->count()} asisten pelatih untuk {$schedule->activity_name} tanggal {$schedule->date->format('d/m/Y')}."
            );
        });

        return back()->with('success', 'Jadwal, deskripsi, dan penugasan asisten pelatih berhasil disimpan.');
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

    /**
     * Include coaches assigned to the actor's branch and coaches available to all branches.
     */
    private function constrainCoachQueryForActor(Builder $coachQuery, User $actor): void
    {
        if (! $actor->isSuperAdmin() && $actor->dojang_id) {
            $coachQuery->where(function (Builder $query) use ($actor): void {
                $query->where('dojang_id', $actor->dojang_id)
                    ->orWhereNull('dojang_id');
            });
        }
    }
}

