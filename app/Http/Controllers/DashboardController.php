<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Dojang;
use App\Models\DutyAssignment;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $salaryService
    ) {}

    /**
     * Display the role-based dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'dojang']);
        $now = Carbon::now('Asia/Jakarta');
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $todayDate = $now->format('Y-m-d');

        // 1. Asisten Pelatih Dashboard
        if ($user->isAsistenPelatih()) {
            $todayAttendance = Attendance::with('photos')
                ->where('user_id', $user->id)
                ->where('date', $todayDate)
                ->latest()
                ->first();

            // Schedue latihan hari ini
            $todaySchedule = TrainingSchedule::with(['dutySchedule.assignments.user'])
                ->where('date', $todayDate)
                ->first();

            // Jika tidak ada hari ini, tampilkan jadwal terdekat berikutnya sebagai referensi
            if (! $todaySchedule) {
                $todaySchedule = TrainingSchedule::with(['dutySchedule.assignments.user'])
                    ->where('date', '>=', $todayDate)
                    ->orderBy('date', 'asc')
                    ->orderBy('start_time', 'asc')
                    ->first();
            }

            // Tugas piket dan materi asisten hari ini
            $todayDuty = null;
            if ($todaySchedule && $todaySchedule->dutySchedule) {
                $todayDuty = $todaySchedule->dutySchedule->assignments
                    ->firstWhere('user_id', $user->id);
            }

            $monthlySummary = $this->salaryService->calculateMonthlySummary($user, $currentMonth, $currentYear);

            $myUpcomingDuties = DutyAssignment::with('dutySchedule.trainingSchedule')
                ->where('user_id', $user->id)
                ->whereHas('dutySchedule.trainingSchedule', function ($q) use ($todayDate) {
                    $q->where('date', '>=', $todayDate);
                })
                ->orderBy('id', 'asc')
                ->take(3)
                ->get();

            $upcomingSchedules = TrainingSchedule::with('dutySchedule.assignments.user')
                ->where('date', '>=', $todayDate)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->take(4)
                ->get();

            $recentAttendances = Attendance::with(['trainingSchedule', 'photos'])
                ->where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->take(5)
                ->get();

            return view('dashboard.asisten', compact(
                'user',
                'todayAttendance',
                'todaySchedule',
                'todayDuty',
                'monthlySummary',
                'myUpcomingDuties',
                'upcomingSchedules',
                'recentAttendances',
                'now'
            ));
        }

        // 2. Admin / Pelatih Utama Dashboard
        if ($user->isAdmin() && ! $user->isSuperAdmin()) {
            $todayAttendances = Attendance::with(['user', 'photos', 'trainingSchedule'])
                ->where('date', $todayDate)
                ->latest()
                ->get();

            $coachQuery = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
                ->where('is_active', true);

            if ($user->dojang_id) {
                $coachQuery->where(function ($query) use ($user) {
                    $query->where('dojang_id', $user->dojang_id)
                        ->orWhereNull('dojang_id');
                });
            }

            $coaches = $coachQuery->get();
            $monthlyAttendanceCounts = Attendance::query()
                ->selectRaw('user_id, COUNT(*) as total_hadir')
                ->where('status', 'Hadir')
                ->whereBetween('date', [
                    $now->copy()->startOfMonth()->toDateString(),
                    $now->copy()->endOfMonth()->toDateString(),
                ])
                ->whereIn('user_id', $coaches->modelKeys())
                ->groupBy('user_id')
                ->pluck('total_hadir', 'user_id');

            $coaches->each(function (User $coach) use ($currentMonth, $currentYear, $monthlyAttendanceCounts) {
                $summary = $this->salaryService->calculateMonthlySummary($coach, $currentMonth, $currentYear);
                $coach->monthly_summary = $summary;
                $coach->monthly_hadir_count = (int) $monthlyAttendanceCounts->get($coach->id, 0);
            });
            $activeAssistantCount = $coaches->count();

            $upcomingSchedules = TrainingSchedule::with('dutySchedule.assignments.user')
                ->where('date', '>=', $todayDate)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->take(5)
                ->get();

            return view('dashboard.admin', compact(
                'user',
                'todayAttendances',
                'coaches',
                'activeAssistantCount',
                'upcomingSchedules',
                'currentMonth',
                'currentYear',
                'now'
            ));
        }

        // 3. Superadmin Dashboard
        $todayAttendances = Attendance::with(['user', 'photos', 'trainingSchedule'])
            ->where('date', $todayDate)
            ->latest()
            ->get();

        $totalDojangs = Dojang::where('is_active', true)->count();
        $totalCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true)->count();
        $totalHeadCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->where('is_active', true)->count();

        $allCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
            ->where('is_active', true)
            ->get();

        $monthlyAttendanceCounts = Attendance::query()
            ->selectRaw('user_id, COUNT(*) as total_hadir')
            ->where('status', 'Hadir')
            ->whereBetween('date', [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ])
            ->whereIn('user_id', $allCoaches->modelKeys())
            ->groupBy('user_id')
            ->pluck('total_hadir', 'user_id');

        $totalHadirThisMonth = 0;
        $totalEstimatedSalaryThisMonth = 0;

        foreach ($allCoaches as $coach) {
            $summary = $this->salaryService->calculateMonthlySummary($coach, $currentMonth, $currentYear);
            $totalHadirThisMonth += $summary['total_approved_sessions'];
            $totalEstimatedSalaryThisMonth += $summary['total_estimated_salary'];
            $coach->monthly_summary = $summary;
            $coach->monthly_hadir_count = (int) $monthlyAttendanceCounts->get($coach->id, 0);
        }

        $coaches = $allCoaches;
        $activeAssistantCount = $allCoaches->count();

        $upcomingSchedules = TrainingSchedule::with('dutySchedule.assignments.user')
            ->where('date', '>=', $todayDate)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        $recentAttendances = Attendance::with(['user', 'photos', 'trainingSchedule'])
            ->latest()
            ->take(8)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        return view('dashboard.superadmin', compact(
            'user',
            'todayAttendances',
            'coaches',
            'activeAssistantCount',
            'totalDojangs',
            'totalCoaches',
            'totalHeadCoaches',
            'totalHadirThisMonth',
            'totalEstimatedSalaryThisMonth',
            'allCoaches',
            'upcomingSchedules',
            'recentAttendances',
            'recentLogs',
            'currentMonth',
            'currentYear',
            'now'
        ));
    }
}
