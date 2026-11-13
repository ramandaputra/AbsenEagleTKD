<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display the reports page.
     */
    public function index(Request $request): View
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $userId = $request->input('user_id');
        $status = $request->input('status');

        $data = $this->reportService->getReportData($startDate, $endDate, $userId ? (int) $userId : null, $status);
        $coaches = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true)->get();

        return view('reports.index', array_merge($data, [
            'coachesList' => $coaches,
            'selectedUserId' => $userId,
            'selectedStatus' => $status,
        ]));
    }

    /**
     * Download Excel / CSV.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        ActivityLog::record(
            $actor->id,
            'EXPORT_EXCEL',
            "{$actor->name} mengunduh laporan rekap absensi Excel/CSV."
        );

        return $this->reportService->exportExcel(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('user_id') ? (int) $request->input('user_id') : null,
            $request->input('status')
        );
    }

    /**
     * Download PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            abort(403);
        }

        ActivityLog::record(
            $actor->id,
            'EXPORT_PDF',
            "{$actor->name} mengunduh laporan rekap absensi PDF resmi."
        );

        return $this->reportService->exportPdf(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('user_id') ? (int) $request->input('user_id') : null,
            $request->input('status')
        );
    }
}

