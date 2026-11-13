<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Dojang;
use App\Models\Payroll;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function __construct(
        protected SalaryCalculationService $salaryService
    ) {}

    /**
     * Display personal monthly attendance & salary summary for Assistant Coach.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $summary = $this->salaryService->calculateMonthlySummary($user, $month, $year);

        return view('salary.index', compact('user', 'month', 'year', 'summary'));
    }

    /**
     * Show printable official salary slip for the logged-in coach.
     */
    public function slip(Request $request, int $month, int $year): View
    {
        $user = $request->user();
        $summary = $this->salaryService->calculateMonthlySummary($user, $month, $year);

        return view('salary.slip', [
            'coach' => $user,
            'month' => $month,
            'year' => $year,
            'summary' => $summary,
            'payroll' => $summary['payroll_record'],
        ]);
    }

    /**
     * Admin & Superadmin payroll management dashboard.
     */
    public function adminIndex(Request $request): View
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);
        $dojangId = $request->input('dojang_id');

        $query = User::with('dojang')
            ->where('role', UserRole::AsistenPelatih)
            ->where('is_active', true);

        if (! $actor->isSuperAdmin()) {
            if ($actor->dojang_id) {
                $query->where('dojang_id', $actor->dojang_id);
            }
        } elseif ($dojangId) {
            $query->where('dojang_id', $dojangId);
        }

        $coaches = $query->get();

        $payrollItems = [];
        $totalPayrollSum = 0;
        $totalSessionsSum = 0;

        foreach ($coaches as $coach) {
            $summary = $this->salaryService->calculateMonthlySummary($coach, $month, $year);
            $payrollItems[] = [
                'coach' => $coach,
                'summary' => $summary,
                'payroll' => $summary['payroll_record'],
            ];
            $totalPayrollSum += $summary['total_estimated_salary'];
            $totalSessionsSum += $summary['total_approved_sessions'];
        }

        $allDojangs = Dojang::where('is_active', true)->get();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('salary.admin-index', compact(
            'actor',
            'month',
            'year',
            'dojangId',
            'allDojangs',
            'payrollItems',
            'totalPayrollSum',
            'totalSessionsSum',
            'monthNames'
        ));
    }

    /**
     * Update the per-session honor rate for an assistant coach.
     */
    public function updateCoachRate(Request $request, User $coach): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin() || ! $coach->isAsistenPelatih()) {
            abort(403);
        }

        if (! $actor->isSuperAdmin() && $actor->dojang_id && $actor->dojang_id !== $coach->dojang_id) {
            abort(403);
        }

        $validated = $request->validate([
            'coach_id' => ['required', 'integer', 'in:'.$coach->id],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
        ]);

        $coach->update([
            'rate_per_session' => $validated['rate_per_session'],
        ]);

        return redirect()->route('dashboard')->with('success', 'Tarif honor per pertemuan untuk '.$coach->name.' berhasil diperbarui.');
    }

    /**
     * Admin view & print salary slip for a specific coach.
     */
    public function adminSlip(Request $request, User $coach, int $month, int $year): View
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        if (! $actor->isSuperAdmin() && $actor->dojang_id && $actor->dojang_id !== $coach->dojang_id) {
            abort(403);
        }

        $summary = $this->salaryService->calculateMonthlySummary($coach, $month, $year);

        return view('salary.slip', [
            'coach' => $coach,
            'month' => $month,
            'year' => $year,
            'summary' => $summary,
            'payroll' => $summary['payroll_record'],
        ]);
    }

    /**
     * Generate or recalculate monthly payroll for all assistant coaches.
     */
    public function adminGenerateAll(Request $request): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $query = User::where('role', UserRole::AsistenPelatih)->where('is_active', true);
        if (! $actor->isSuperAdmin() && $actor->dojang_id) {
            $query->where('dojang_id', $actor->dojang_id);
        }

        $coaches = $query->get();
        $generatedCount = 0;

        foreach ($coaches as $coach) {
            $existing = Payroll::where('user_id', $coach->id)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->first();

            $bonus = $existing ? (float) $existing->bonus_allowance : 0.00;
            $deductions = $existing ? (float) $existing->deductions : 0.00;

            $this->salaryService->generateMonthlyPayroll(
                $coach,
                $month,
                $year,
                $bonus,
                $deductions,
                $actor,
                $existing ? $existing->payment_method : PaymentMethod::Transfer,
                $existing ? $existing->payment_notes : null
            );

            $generatedCount++;
        }

        return back()->with('success', "Berhasil menghitung dan memperbarui rekap gaji untuk {$generatedCount} asisten pelatih periode {$month}/{$year}.");
    }

    /**
     * Update adjustments (bonus/deductions) for a coach payroll.
     */
    public function updateAdjustment(Request $request, User $coach): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
            'bonus_allowance' => ['required', 'numeric', 'min:0'],
            'deductions' => ['required', 'numeric', 'min:0'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->salaryService->generateMonthlyPayroll(
            $coach,
            $validated['month'],
            $validated['year'],
            (float) $validated['bonus_allowance'],
            (float) $validated['deductions'],
            $actor,
            PaymentMethod::Transfer,
            $validated['payment_notes']
        );

        return back()->with('success', 'Rincian bonus & potongan honor berhasil diperbarui.');
    }

    /**
     * Update payment status (mark as Paid / Lunas or Approved).
     */
    public function updateStatus(Request $request, Payroll $payroll): RedirectResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_status' => ['required', 'in:draft,approved,paid'],
            'payment_method' => ['required', 'in:transfer,cash'],
            'payment_date' => ['nullable', 'date'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payroll->update([
            'payment_status' => PaymentStatus::from($validated['payment_status']),
            'payment_method' => PaymentMethod::from($validated['payment_method']),
            'payment_date' => $validated['payment_status'] === 'paid' ? ($validated['payment_date'] ?? Carbon::now()->format('Y-m-d')) : null,
            'payment_notes' => $validated['payment_notes'],
            'approved_by' => $actor->id,
            'approved_at' => Carbon::now(),
        ]);

        $statusLabel = $payroll->payment_status->label();

        return back()->with('success', "Status pembayaran gaji asisten pelatih berhasil diubah menjadi: {$statusLabel}.");
    }
}

