<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\DutyAssignment;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiDashboardController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $salaryService
    ) {}

    /**
     * Get role-based mobile dashboard payload.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');
        $now = Carbon::now();
        $todayStr = $now->format('Y-m-d');
        $startOfMonth = $now->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth = $now->copy()->endOfMonth()->format('Y-m-d');

        // 1. Dashboard Asisten Pelatih (User)
        if ($user->isAsistenPelatih()) {
            $monthlySummary = $this->salaryService->calculateMonthlySummary($user, $now->month, $now->year);

            $todayAttendance = Attendance::with('photos')
                ->where('user_id', $user->id)
                ->where('date', $todayStr)
                ->first();

            $nextDuty = DutyAssignment::with('dutySchedule.trainingSchedule')
                ->where('user_id', $user->id)
                ->whereHas('dutySchedule.trainingSchedule', function ($q) use ($todayStr) {
                    $q->where('date', '>=', $todayStr);
                })
                ->orderBy('id', 'asc')
                ->first();

            $upcomingSchedules = TrainingSchedule::with('dutySchedule.assignments.user')
                ->where('date', '>=', $todayStr)
                ->orderBy('date', 'asc')
                ->orderBy('start_time', 'asc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'role' => 'asisten_pelatih',
                'data' => [
                    'user' => $user,
                    'today_attendance' => $todayAttendance,
                    'next_duty_assignment' => $nextDuty,
                    'monthly_summary' => [
                        'month_name' => $monthlySummary['month_name'],
                        'year' => $monthlySummary['year'],
                        'hadir' => $monthlySummary['total_approved_sessions'],
                        'izin' => $monthlySummary['total_izin_sessions'],
                        'sakit' => $monthlySummary['total_sakit_sessions'],
                        'alpa' => $monthlySummary['total_alpa_sessions'],
                        'rate_per_session' => $monthlySummary['rate_per_session'],
                        'base_salary' => $monthlySummary['base_salary'],
                        'total_salary' => $monthlySummary['total_estimated_salary'],
                        'payment_status' => $monthlySummary['payroll_record'] ? $monthlySummary['payroll_record']->payment_status->value : 'draft',
                    ],
                    'upcoming_schedules' => $upcomingSchedules,
                ],
            ]);
        }

        // 2. Dashboard Admin / Pelatih Utama & Superadmin
        $totalUsers = User::where('is_active', true)->count();
        $totalCoaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true)->count();

        $monthAttendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])->get();
        $countHadir = $monthAttendances->where('status', 'Hadir')->count();
        $countIzin = $monthAttendances->where('status', 'Izin')->count();
        $countSakit = $monthAttendances->where('status', 'Sakit')->count();
        $countAlpa = $monthAttendances->where('status', 'Alpa')->count();

        // 7-day stats for chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => $d,
                'day_name' => $now->copy()->subDays($i)->translatedFormat('D'),
                'hadir' => Attendance::where('date', $d)->where('status', 'Hadir')->count(),
                'izin' => Attendance::where('date', $d)->where('status', 'Izin')->count(),
                'sakit' => Attendance::where('date', $d)->where('status', 'Sakit')->count(),
                'alpa' => Attendance::where('date', $d)->where('status', 'Alpa')->count(),
            ];
        }

        $upcomingSchedules = TrainingSchedule::with(['dutySchedule.assignments.user'])
            ->where('date', '>=', $todayStr)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->take(5)
            ->get();

        $recentAttendances = Attendance::with(['user', 'photos', 'trainingSchedule'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->take(8)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'role' => $user->role_name,
            'data' => [
                'user' => $user,
                'metrics' => [
                    'total_users' => $totalUsers,
                    'total_coaches' => $totalCoaches,
                    'month_hadir' => $countHadir,
                    'month_izin' => $countIzin,
                    'month_sakit' => $countSakit,
                    'month_alpa' => $countAlpa,
                ],
                'chart_statistics' => $chartData,
                'upcoming_schedules' => $upcomingSchedules,
                'recent_attendances' => $recentAttendances,
                'recent_activity_logs' => $recentLogs,
            ],
        ]);
    }
}

