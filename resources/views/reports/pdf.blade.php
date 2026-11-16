<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Kehadiran Pelatih</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 2px solid #00288E; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; color: #00288E; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 11px; color: #64748b; margin: 2px 0 0 0; }
        .period-badge { display: inline-block; padding: 3px 8px; background: #EAEDFF; color: #00288E; font-weight: bold; border-radius: 4px; font-size: 10px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        th { background: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        td { padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .status-hadir { color: #166534; font-weight: bold; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 15px; }
        .footer { margin-top: 30px; }
        .sig-col { width: 50%; float: left; text-align: center; }
        .sig-space { height: 45px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;">
                    <h1 class="title">EAGLE TAEKWONDO CLUB</h1>
                    <p class="subtitle">Laporan Rekapitulasi Kehadiran &amp; Honor Pelatih</p>
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
                <td style="border: none; padding: 2px 5px;"><strong>Total Sesi Hadir:</strong> {{ $total_hadir }} Sesi</td>
                <td style="border: none; padding: 2px 5px;"><strong>Asisten Aktif:</strong> {{ $active_coaches_count }} Pelatih</td>
                <td style="border: none; padding: 2px 5px; text-align: right;"><strong>Total Akumulasi Honor:</strong> Rp {{ number_format($total_salary_overall, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Tabel Rekap Pelatih -->
    <h3 style="font-size: 11px; margin: 10px 0 4px 0; text-transform: uppercase;">1. Rekapitulasi Kehadiran (Hadir) per Asisten Pelatih</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Asisten Pelatih</th>
                <th>Sabuk</th>
                <th class="text-center">Sesi Hadir</th>
                <th class="text-right">Tarif / Sesi</th>
                <th class="text-right">Total Honor</th>
                <th>Tanggal Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($coaches as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $c['coach']->name }}</td>
                    <td>{{ $c['coach']->belt_level ?: '-' }}</td>
                    <td class="text-center status-hadir">{{ $c['hadir_count'] }}</td>
                    <td class="text-right">Rp {{ number_format($c['coach']->rate_per_session, 0, ',', '.') }}</td>
                    <td class="text-right font-bold status-hadir">Rp {{ number_format($c['total_salary'], 0, ',', '.') }}</td>
                    <td>
                        @if(!empty($c['attended_days_list']))
                            {{ implode(', ', array_map(fn($d) => date('d/m', strtotime($d)), $c['attended_days_list'])) }}
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Tabel Log Absensi Terkini -->
    <h3 style="font-size: 11px; margin: 15px 0 4px 0; text-transform: uppercase;">2. Log Riwayat Presensi Hadir Terverifikasi Server</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama Pelatih</th>
                <th>Kegiatan Latihan</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances_list as $i => $att)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $att->date->translatedFormat('d F Y') }}</td>
                    <td>{{ substr($att->time, 0, 5) }} WIB</td>
                    <td class="font-bold">{{ $att->user->name }}</td>
                    <td>{{ $att->trainingSchedule ? $att->trainingSchedule->activity_name : 'Latihan Mandiri' }}</td>
                    <td class="text-center status-hadir">Hadir</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #94a3b8; padding: 12px;">Tidak ada riwayat kehadiran tercatat</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="sig-col">
            <p>Mengetahui,<br><strong>Pelatih Utama</strong></p>
            <div class="sig-space"></div>
            <p><strong>( Lukman )</strong></p>
        </div>
        <div class="sig-col">
            <p>Tanjungpinang, {{ date('d F Y') }}<br><strong>Pimpinan / Owner</strong></p>
            <div class="sig-space"></div>
            <p><strong>( Superadmin / Mingle )</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
