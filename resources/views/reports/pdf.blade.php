<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Absensi & Piket Mengajar Pelatih</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 2px solid #dc2626; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 11px; color: #64748b; margin: 2px 0 0 0; }
        .period-badge { display: inline-block; padding: 3px 8px; background: #fee2e2; color: #991b1b; font-weight: bold; border-radius: 4px; font-size: 10px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        th { background: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        td { padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .status-hadir { color: #166534; font-weight: bold; }
        .status-izin { color: #854d0e; font-weight: bold; }
        .status-sakit { color: #1e40af; font-weight: bold; }
        .status-alpa { color: #991b1b; font-weight: bold; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px; }
        .footer { margin-top: 30px; }
        .sig-col { width: 33.33%; float: left; text-align: center; }
        .sig-space { height: 45px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;">
                    <h1 class="title">EAGLE TAEKWONDO CLUB</h1>
                    <p class="subtitle">Laporan Rekapitulasi Absensi & Penjadwalan Piket Mengajar Pelatih</p>
                    <span class="period-badge">Periode: {{ $start_date }} s/d {{ $end_date }}</span>
                </td>
                <td style="border: none; text-align: right; vertical-align: top;">
                    <p style="margin: 0; font-size: 9px; color: #64748b;">Tanggal Cetak: {{ date('d/m/Y H:i') }} WIB</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="summary-box">
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px 5px;"><strong>Total Sesi Hadir:</strong> {{ $total_hadir }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Total Izin:</strong> {{ $total_izin }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Total Sakit:</strong> {{ $total_sakit }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Total Alpa:</strong> {{ $total_alpa }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Total Penugasan Piket:</strong> {{ $total_duties_overall }}</td>
            </tr>
        </table>
    </div>

    <!-- Tabel Rekap Pelatih -->
    <h3 style="font-size: 11px; margin: 10px 0 4px 0; text-transform: uppercase;">1. Rekapitulasi Kehadiran & Tugas Mengajar per Asisten Pelatih</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Asisten Pelatih</th>
                <th>Sabuk</th>
                <th class="text-center">Tugas Piket</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th class="text-center">Alpa</th>
                <th class="text-center">% Kehadiran</th>
                <th class="text-right">Total Honor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coaches as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $c['coach']->name }}</td>
                    <td>{{ $c['coach']->belt_level ?: '-' }}</td>
                    <td class="text-center font-bold">{{ $c['total_duties'] }}</td>
                    <td class="text-center status-hadir">{{ $c['hadir_count'] }}</td>
                    <td class="text-center status-izin">{{ $c['izin_count'] }}</td>
                    <td class="text-center status-sakit">{{ $c['sakit_count'] }}</td>
                    <td class="text-center status-alpa">{{ $c['alpa_count'] }}</td>
                    <td class="text-center font-bold">{{ $c['attendance_rate'] }}%</td>
                    <td class="text-right font-bold">Rp {{ number_format($c['total_salary'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tabel Log Absensi Terkini -->
    <h3 style="font-size: 11px; margin: 15px 0 4px 0; text-transform: uppercase;">2. Log Riwayat Absensi Terverifikasi Server</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama Pelatih</th>
                <th>Kegiatan Latihan</th>
                <th class="text-center">Status</th>
                <th>Keterangan / Materi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances_list->take(25) as $idx => $att)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $att->date->format('d/m/Y') }}</td>
                    <td>{{ substr($att->time, 0, 5) }} WIB</td>
                    <td class="font-bold">{{ $att->user->name }}</td>
                    <td>{{ $att->trainingSchedule ? $att->trainingSchedule->activity_name : 'Latihan Mandiri' }}</td>
                    <td class="text-center">
                        <span class="status-{{ strtolower($att->status) }}">{{ $att->status }}</span>
                    </td>
                    <td>{{ $att->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 12px; color: #94a3b8;">Tidak ada data absensi dalam rentang periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <div class="footer">
        <div class="sig-col">
            <p style="margin: 0; font-size: 10px;">Mengetahui,</p>
            <p style="margin: 2px 0 0 0; font-weight: bold;">Ketua / Owner Dojang</p>
            <div class="sig-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Grand Master Bambang S.</p>
            <p style="margin: 0; font-size: 9px; color: #64748b;">Dan 7 Kukkiwon</p>
        </div>

        <div class="sig-col">
            <p style="margin: 0; font-size: 10px;">Diverifikasi oleh,</p>
            <p style="margin: 2px 0 0 0; font-weight: bold;">Pelatih Utama (Head Coach)</p>
            <div class="sig-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Sabeum Nim Dimas W.</p>
            <p style="margin: 0; font-size: 9px; color: #64748b;">Dan 4 Kukkiwon</p>
        </div>

        <div class="sig-col">
            <p style="margin: 0; font-size: 10px;">Dibuat Tanggal,</p>
            <p style="margin: 2px 0 0 0; font-weight: bold;">Admin Sistem</p>
            <div class="sig-space"></div>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Administrator</p>
            <p style="margin: 0; font-size: 9px; color: #64748b;">Eagle Taekwondo Club</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>

