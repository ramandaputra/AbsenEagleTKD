<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DutyAssignment;
use App\Models\TrainingSchedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Get attendance and duty report data based on filters.
     *
     * @return array{
     *     coaches: array<int, array{
     *         coach: User,
     *         hadir_count: int,
     *         izin_count: int,
     *         sakit_count: int,
     *         alpa_count: int,
     *         total_duties: int,
     *         attendance_rate: float,
     *         total_salary: float
     *     }>,
     *     total_hadir: int,
     *     total_izin: int,
     *     total_sakit: int,
     *     total_alpa: int,
     *     total_duties_overall: int,
     *     attendances_list: \Illuminate\Database\Eloquent\Collection<int, Attendance>
     * }
     */
    public function getReportData(?string $startDate = null, ?string $endDate = null, ?int $userId = null, ?string $status = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $attendanceQuery = Attendance::with(['user.role', 'trainingSchedule', 'photos', 'dutyAssignment'])
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);

        if ($userId) {
            $attendanceQuery->where('user_id', $userId);
        }
        if ($status) {
            $attendanceQuery->where('status', $status);
        }

        $attendancesList = $attendanceQuery->orderBy('date', 'desc')->orderBy('time', 'desc')->get();

        $coachesQuery = User::whereHas('role', fn ($q) => $q->where('name', 'asisten_pelatih'))->where('is_active', true);
        if ($userId) {
            $coachesQuery->where('id', $userId);
        }
        $coaches = $coachesQuery->get();

        $coachesReport = [];
        $totalHadir = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalAlpa = 0;
        $totalDutiesOverall = 0;

        foreach ($coaches as $coach) {
            $coachAttendances = $attendancesList->where('user_id', $coach->id);

            $hadir = $coachAttendances->where('status', 'Hadir')->count();
            $izin = $coachAttendances->where('status', 'Izin')->count();
            $sakit = $coachAttendances->where('status', 'Sakit')->count();
            $alpa = $coachAttendances->where('status', 'Alpa')->count();

            // Total duty assignments within date range
            $dutyCount = DutyAssignment::where('user_id', $coach->id)
                ->whereHas('dutySchedule.trainingSchedule', function ($q) use ($start, $end) {
                    $q->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
                })
                ->count();

            $totalActivity = $dutyCount > 0 ? $dutyCount : ($hadir + $izin + $sakit + $alpa);
            $attendanceRate = $totalActivity > 0 ? round(($hadir / $totalActivity) * 100, 1) : 0.0;
            $totalSalary = $hadir * (float) $coach->rate_per_session;

            $coachesReport[] = [
                'coach' => $coach,
                'hadir_count' => $hadir,
                'izin_count' => $izin,
                'sakit_count' => $sakit,
                'alpa_count' => $alpa,
                'total_duties' => $dutyCount,
                'attendance_rate' => $attendanceRate,
                'total_salary' => $totalSalary,
            ];

            $totalHadir += $hadir;
            $totalIzin += $izin;
            $totalSakit += $sakit;
            $totalAlpa += $alpa;
            $totalDutiesOverall += $dutyCount;
        }

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'coaches' => $coachesReport,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpa' => $totalAlpa,
            'total_duties_overall' => $totalDutiesOverall,
            'attendances_list' => $attendancesList,
        ];
    }

    /**
     * Export report to Excel / CSV format.
     */
    public function exportExcel(?string $startDate = null, ?string $endDate = null, ?int $userId = null, ?string $status = null): StreamedResponse
    {
        $data = $this->getReportData($startDate, $endDate, $userId, $status);
        $filename = 'Laporan_Absensi_Eagle_TKD_'.$data['start_date'].'_sd_'.$data['end_date'].'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Title
            fputcsv($handle, ['REKAPITULASI ABSENSI DAN PIKET MENGAJAR PELATIH EAGLE TAEKWONDO']);
            fputcsv($handle, ['Periode', $data['start_date'].' s/d '.$data['end_date']]);
            fputcsv($handle, []);

            // Summary Table Header
            fputcsv($handle, ['No', 'Nama Asisten Pelatih', 'Sabuk', 'Tugas Piket', 'Hadir', 'Izin', 'Sakit', 'Alpa', '% Kehadiran', 'Tarif/Sesi', 'Total Honor']);

            foreach ($data['coaches'] as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['coach']->name,
                    $row['coach']->belt_level ?: '-',
                    $row['total_duties'],
                    $row['hadir_count'],
                    $row['izin_count'],
                    $row['sakit_count'],
                    $row['alpa_count'],
                    $row['attendance_rate'].'%',
                    $row['coach']->rate_per_session,
                    $row['total_salary'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['RINCIAN RIWAYAT ABSENSI']);
            fputcsv($handle, ['No', 'Tanggal', 'Jam', 'Nama Pelatih', 'Kegiatan Latihan', 'Status', 'Keterangan']);

            foreach ($data['attendances_list'] as $idx => $att) {
                fputcsv($handle, [
                    $idx + 1,
                    $att->date->format('Y-m-d'),
                    substr($att->time, 0, 5),
                    $att->user->name,
                    $att->trainingSchedule ? $att->trainingSchedule->activity_name : 'Latihan Mandiri',
                    $att->status,
                    $att->notes ?: '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export report to PDF format.
     */
    public function exportPdf(?string $startDate = null, ?string $endDate = null, ?int $userId = null, ?string $status = null): Response
    {
        $data = $this->getReportData($startDate, $endDate, $userId, $status);

        $pdf = Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'Laporan_Absensi_Eagle_TKD_'.$data['start_date'].'_sd_'.$data['end_date'].'.pdf';

        return $pdf->download($filename);
    }
}

