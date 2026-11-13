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
        $now = Carbon::now();
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

            $coaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
                ->where('is_active', true)
                ->get()
                ->map(function (User $coach) use ($currentMonth, $currentYear) {
                    $summary = $this->salaryService->calculateMonthlySummary($coach, $currentMonth, $currentYear);
                    $coach->monthly_summary = $summary;

                    return $coach;
                });

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
                'upcomingSchedules',
                'currentMonth',
                'currentYear',
                'now'
            ));
        }

        // 3. Superadmin Dashboard
        $totalDojangs = Dojang::where('is_active', true)->count();
        $totalCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true)->count();
        $totalHeadCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'admin'))->where('is_active', true)->count();

        $allCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))
            ->where('is_active', true)
            ->get();

        $totalHadirThisMonth = 0;
        $totalEstimatedSalaryThisMonth = 0;

        foreach ($allCoaches as $coach) {
            $summary = $this->salaryService->calculateMonthlySummary($coach, $currentMonth, $currentYear);
            $totalHadirThisMonth += $summary['total_approved_sessions'];
            $totalEstimatedSalaryThisMonth += $summary['total_estimated_salary'];
            $coach->monthly_summary = $summary;
        }

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
            ->take(6)
            ->get();

        return view('dashboard.superadmin', compact(
            'user',
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
