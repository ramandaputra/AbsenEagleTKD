<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $coachQuery = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
            ->where('is_active', true);

        if (! $user->isSuperAdmin() && $user->dojang_id) {
            $coachQuery->where('dojang_id', $user->dojang_id);
        }

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
            'coaches' => ['required', 'array', 'min:1'],
            'coaches.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
        ]);

        $coachQuery = User::whereKey($validated['coaches'])
            ->whereHas('role', fn ($query) => $query->where('name', 'asisten_pelatih'))
            ->where('is_active', true);

        if (! $actor->isSuperAdmin() && $actor->dojang_id) {
            $coachQuery->where('dojang_id', $actor->dojang_id);
        }

        $coaches = $coachQuery->get();

        if ($coaches->count() !== count($validated['coaches'])) {
            return back()
                ->withErrors(['coaches' => 'Pilih asisten pelatih aktif yang tersedia untuk dojang Anda.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $actor, $coaches) {
            $schedule = TrainingSchedule::firstOrCreate(
                [
                    'activity_name' => 'Latihan Reguler',
                    'date' => $validated['date'],
                ],
                [
                    'start_time' => '16:00:00',
                    'end_time' => '18:00:00',
                    'location' => $actor->dojang?->address ?: ($actor->dojang?->name ?? 'Dojang Eagle Pusat GOR Pajajaran'),
                    'created_by' => $actor->id,
                ]
            );

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
                "{$actor->name} menetapkan {$coaches->count()} asisten pelatih untuk latihan reguler tanggal {$schedule->date->format('d/m/Y')}."
            );
        });

        return back()->with('success', 'Jadwal dan penugasan asisten pelatih berhasil disimpan.');
    }

    /**
     * Save today's training description, creating the default schedule when needed.
     */
    public function updateTodayMaterials(Request $request): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        if ($validated['date'] !== Carbon::now()->toDateString()) {
            return back()
                ->withErrors(['date' => 'Materi hanya dapat disimpan untuk tanggal hari ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $actor) {
            $schedule = TrainingSchedule::firstOrCreate(
                [
                    'activity_name' => 'Latihan Reguler',
                    'date' => $validated['date'],
                ],
                [
                    'start_time' => '16:00:00',
                    'end_time' => '18:00:00',
                    'location' => $actor->dojang?->address ?: ($actor->dojang?->name ?? 'Dojang Eagle Pusat GOR Pajajaran'),
                    'created_by' => $actor->id,
                ]
            );

            $schedule->update([
                'description' => $validated['description'],
            ]);

            ActivityLog::record(
                $actor->id,
                'UPDATE_TRAINING_MATERIAL',
                "{$actor->name} menyimpan materi latihan tanggal {$schedule->date->format('d/m/Y')}."
            );
        });

        return redirect()->route('dashboard')->with('success', 'Materi latihan hari ini berhasil diperbarui.');
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

