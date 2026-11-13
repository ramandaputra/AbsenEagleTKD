<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApiReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Get report recap data.
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userId = $request->input('user_id');
        $status = $request->input('status');

        $data = $this->reportService->getReportData($startDate, $endDate, $userId ? (int) $userId : null, $status);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export report to Excel / CSV.
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
            "{$actor->name} mengunduh laporan absensi dalam format Excel/CSV."
        );

        return $this->reportService->exportExcel(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('user_id') ? (int) $request->input('user_id') : null,
            $request->input('status')
        );
    }

    /**
     * Export report to PDF.
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
            "{$actor->name} mengunduh laporan absensi dalam format PDF resmi."
        );

        return $this->reportService->exportPdf(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('user_id') ? (int) $request->input('user_id') : null,
            $request->input('status')
        );
    }
}

