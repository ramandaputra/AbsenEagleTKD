<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiSalaryController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $salaryService
    ) {}

    /**
     * Get monthly salary calculation & attendance count for the authenticated coach.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $summary = $this->salaryService->calculateMonthlySummary($user, $month, $year);

        return response()->json([
            'success' => true,
            'data' => [
                'coach' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'belt_level' => $user->belt_level,
                    'rate_per_session' => (float) $user->rate_per_session,
                    'bank_name' => $user->bank_name,
                    'bank_account_number' => $user->bank_account_number,
                    'bank_account_holder' => $user->bank_account_holder,
                ],
                'month' => $summary['month'],
                'year' => $summary['year'],
                'month_name' => $summary['month_name'],
                'total_approved_sessions' => $summary['total_approved_sessions'],
                'total_pending_sessions' => $summary['total_pending_sessions'],
                'total_rejected_sessions' => $summary['total_rejected_sessions'],
                'rate_per_session' => $summary['rate_per_session'],
                'base_salary' => $summary['base_salary'],
                'bonus_allowance' => $summary['estimated_bonus'],
                'deductions' => $summary['estimated_deductions'],
                'total_salary' => $summary['total_estimated_salary'],
                'payment_status' => $summary['payroll_record'] ? $summary['payroll_record']->payment_status->value : 'draft',
                'payment_status_label' => $summary['payroll_record'] ? $summary['payroll_record']->payment_status->label() : 'Draft Perhitungan',
                'payment_method' => $summary['payroll_record'] ? $summary['payroll_record']->payment_method->value : 'transfer',
                'payment_date' => $summary['payroll_record']?->payment_date?->format('Y-m-d'),
                'payment_notes' => $summary['payroll_record']?->payment_notes,
                'attendances' => $summary['attendances'],
            ],
        ]);
    }

    /**
     * Admin: get payroll list for all coaches in month.
     */
    public function adminPayrolls(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $query = User::with('dojang')
            ->where('role', UserRole::AsistenPelatih)
            ->where('is_active', true);

        if ($actor->role === UserRole::Admin && $actor->dojang_id) {
            $query->where('dojang_id', $actor->dojang_id);
        }

        $coaches = $query->get();
        $payrollList = [];
        $totalSum = 0;

        foreach ($coaches as $coach) {
            $summary = $this->salaryService->calculateMonthlySummary($coach, $month, $year);
            $payrollList[] = [
                'coach_id' => $coach->id,
                'coach_name' => $coach->name,
                'belt_level' => $coach->belt_level,
                'dojang_name' => $coach->dojang?->name,
                'rate_per_session' => $summary['rate_per_session'],
                'total_approved_sessions' => $summary['total_approved_sessions'],
                'base_salary' => $summary['base_salary'],
                'bonus_allowance' => $summary['estimated_bonus'],
                'deductions' => $summary['estimated_deductions'],
                'total_salary' => $summary['total_estimated_salary'],
                'payment_status' => $summary['payroll_record'] ? $summary['payroll_record']->payment_status->value : 'draft',
                'payment_status_label' => $summary['payroll_record'] ? $summary['payroll_record']->payment_status->label() : 'Draft Perhitungan',
            ];
            $totalSum += $summary['total_estimated_salary'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'total_salary_sum' => $totalSum,
                'coaches_count' => count($payrollList),
                'payrolls' => $payrollList,
            ],
        ]);
    }
}

