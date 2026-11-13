<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;

class SalaryCalculationService
{
    /**
     * Calculate monthly attendance and salary preview for an assistant coach.
     *
     * @return array{
     *     month: int,
     *     year: int,
     *     month_name: string,
     *     rate_per_session: float,
     *     total_approved_sessions: int,
     *     total_izin_sessions: int,
     *     total_sakit_sessions: int,
     *     total_alpa_sessions: int,
     *     base_salary: float,
     *     estimated_bonus: float,
     *     estimated_deductions: float,
     *     total_estimated_salary: float,
     *     payroll_record: ?Payroll,
     *     attendances: \Illuminate\Database\Eloquent\Collection<int, Attendance>
     * }
     */
    public function calculateMonthlySummary(User $coach, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $attendances = Attendance::with(['trainingSchedule', 'photos', 'dutyAssignment.dutySchedule'])
            ->where('user_id', $coach->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        $hadirCount = $attendances->where('status', 'Hadir')->count();
        $izinCount = $attendances->where('status', 'Izin')->count();
        $sakitCount = $attendances->where('status', 'Sakit')->count();
        $alpaCount = $attendances->where('status', 'Alpa')->count();

        $ratePerSession = (float) $coach->rate_per_session;
        $baseSalary = $hadirCount * $ratePerSession;

        // Check if formal payroll record already exists
        $payroll = Payroll::where('user_id', $coach->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        $bonus = $payroll ? (float) $payroll->bonus_allowance : 0.00;
        $deductions = $payroll ? (float) $payroll->deductions : 0.00;
        $totalSalary = $payroll ? (float) $payroll->total_salary : ($baseSalary + $bonus - $deductions);

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return [
            'month' => $month,
            'year' => $year,
            'month_name' => $monthNames[$month] ?? (string) $month,
            'rate_per_session' => $ratePerSession,
            'total_approved_sessions' => $hadirCount,
            'attendance_count' => $hadirCount,
            'total_izin_sessions' => $izinCount,
            'total_sakit_sessions' => $sakitCount,
            'total_alpa_sessions' => $alpaCount,
            'base_salary' => $baseSalary,
            'estimated_bonus' => $bonus,
            'estimated_deductions' => $deductions,
            'total_estimated_salary' => $totalSalary,
            'total_salary' => $totalSalary,
            'payroll_record' => $payroll,
            'attendances' => $attendances,
        ];
    }

    /**
     * Generate or update monthly payroll record.
     */
    public function generateMonthlyPayroll(
        User $coach,
        int $month,
        int $year,
        float $bonus = 0.00,
        float $deductions = 0.00,
        ?User $actor = null,
        PaymentMethod $method = PaymentMethod::Transfer,
        ?string $notes = null
    ): Payroll {
        $summary = $this->calculateMonthlySummary($coach, $month, $year);
        $approvedCount = $summary['total_approved_sessions'];
        $rate = (float) $coach->rate_per_session;
        $base = $approvedCount * $rate;
        $total = max(0, $base + $bonus - $deductions);

        return Payroll::updateOrCreate(
            [
                'user_id' => $coach->id,
                'period_month' => $month,
                'period_year' => $year,
            ],
            [
                'total_sessions_attended' => $approvedCount,
                'rate_per_session' => $rate,
                'base_salary' => $base,
                'bonus_allowance' => $bonus,
                'deductions' => $deductions,
                'total_salary' => $total,
                'payment_status' => PaymentStatus::Approved,
                'payment_method' => $method,
                'payment_notes' => $notes,
                'approved_by' => $actor?->id,
                'approved_at' => now(),
            ]
        );
    }
}
