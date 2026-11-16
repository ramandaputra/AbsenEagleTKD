<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Pelatih - {{ $start_date }} sd {{ $end_date }}</title>
    <style>
        @page {
            margin: 25px 30px 20px 30px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #00288E;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-title {
            font-size: 15px;
            font-weight: 800;
            color: #00288E;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #1e293b;
            margin: 2px 0 0 0;
        }
        .kop-address {
            font-size: 8.5px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .kop-date {
            font-size: 8.5px;
            color: #64748b;
            text-align: right;
        }

        /* Dokumen Title */
        .doc-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-period {
            font-size: 9.5px;
            color: #475569;
            margin: 2px 0 0 0;
            font-weight: 600;
        }

        /* Ringkasan Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        .summary-table td {
            border: none;
            padding: 6px 12px;
            font-size: 9.5px;
        }
        .summary-label {
            color: #64748b;
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 700;
            display: block;
        }
        .summary-val {
            font-size: 12px;
            font-weight: 800;
            color: #00288E;
            margin-top: 1px;
        }
        .summary-val.emerald {
            color: #15803d;
        }

        /* Tabel Rekap */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .data-table th {
            background: #F1F5F9;
            color: #334155;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 6px;
            border: 1px solid #CBD5E1;
            text-align: left;
        }
        .data-table td {
            font-size: 9px;
            padding: 5px 6px;
            border: 1px solid #CBD5E1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background: #FAFAFA;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .status-hadir {
            color: #15803d;
            font-weight: 700;
        }
        .date-chip {
            display: inline-block;
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            margin: 1px 1px;
            white-space: nowrap;
        }

        /* Total Row */
        .total-row td {
            background: #EAEDFF !important;
            font-weight: 800;
            color: #00288E;
            border-top: 2px solid #00288E;
            font-size: 9.5px;
        }

        /* Notes Box */
        .note-text {
            font-size: 8px;
            color: #64748b;
            font-style: italic;
            margin-top: 4px;
            margin-bottom: 18px;
        }

        /* Signature Area */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sig-table td {
            border: none;
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5px;
            padding: 0 15px;
        }
        .sig-space {
            height: 48px;
        }
        .sig-name {
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            padding-bottom: 1px;
            min-width: 130px;
        }
        .sig-role {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 70%;">
                <h1 class="kop-title">EAGLE TAEKWONDO CLUB</h1>
                <p class="kop-subtitle">Pusat Pelatihan Beladiri Taekwondo Indonesia &bull; Dojang Resmi</p>
                <p class="kop-address">Jl. Mt Haryono Km.4 Kel. Tanjung Unggat Kec. Bukit Bestari Kota Tanjungpinang</p>
            </td>
            <td style="width: 30%;" class="kop-date">
                <p style="margin: 0;">Dicetak pada:</p>
                <p style="margin: 1px 0 0 0; font-weight: 700; color: #0f172a;">{{ date('d F Y, H:i') }} WIB</p>
                <p style="margin: 1px 0 0 0;">Oleh: Administrator</p>
            </td>
        </tr>
    </table>

    <!-- Judul Dokumen -->
    <div class="doc-header">
        <h2 class="doc-title">REKAPITULASI KEHADIRAN &amp; HONOR ASISTEN PELATIH</h2>
        <p class="doc-period">Periode: {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- Ringkasan Eksekutif (Hanya Menghitung Hadir) -->
    <table class="summary-table">
        <tr>
            <td style="width: 33.3%;">
                <span class="summary-label">Total Sesi Hadir</span>
                <span class="summary-val emerald">{{ $total_hadir }} Sesi</span>
            </td>
            <td style="width: 33.3%; text-align: center;">
                <span class="summary-label">Pelatih Aktif Hadir</span>
                <span class="summary-val">{{ $active_coaches_count }} Pelatih</span>
            </td>
            <td style="width: 33.3%; text-align: right;">
                <span class="summary-label">Total Akumulasi Honor</span>
                <span class="summary-val emerald">Rp {{ number_format($total_salary_overall, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabel Rekapitulasi Hadir Utama -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 24px;" class="text-center">No</th>
                <th style="width: 110px;">Nama Asisten Pelatih</th>
                <th style="width: 65px;">Sabuk</th>
                <th style="width: 65px;" class="text-right">Tarif/Sesi</th>
                <th style="width: 55px;" class="text-center">Sesi Hadir</th>
                <th style="width: 80px;" class="text-right">Total Honor</th>
                <th>Tanggal Hadir dalam Periode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coaches as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $c['coach']->name }}</td>
                    <td>{{ $c['coach']->belt_level ?: 'Pelatih' }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($c['coach']->rate_per_session, 0, ',', '.') }}</td>
                    <td class="text-center status-hadir font-bold">{{ $c['hadir_count'] }}</td>
                    <td class="text-right status-hadir font-bold">Rp {{ number_format($c['total_salary'], 0, ',', '.') }}</td>
                    <td>
                        @if(!empty($c['attended_days_list']))
                            @foreach($c['attended_days_list'] as $dayDate)
                                <span class="date-chip">{{ date('d/m', strtotime($dayDate)) }}</span>
                            @endforeach
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Tidak ada sesi hadir</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #94a3b8; padding: 15px;">Belum ada data pelatih tercatat</td>
                </tr>
            @endforelse

            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $total_hadir }} Sesi</td>
                <td class="text-right">Rp {{ number_format($total_salary_overall, 0, ',', '.') }}</td>
                <td>{{ $active_coaches_count }} Asisten Pelatih Aktif</td>
            </tr>
        </tbody>
    </table>

    <p class="note-text">
        * Dokumen ini sah dan diterbitkan secara elektronik oleh sistem Absensi Eagle Taekwondo Club. Perhitungan honor didasarkan murni pada presensi Hadir terverifikasi.
    </p>

    <!-- Pengesahan & Tanda Tangan -->
    <table class="sig-table">
        <tr>
            <td>
                <p style="margin: 0;">Mengetahui,</p>
                <p style="margin: 2px 0 0 0; font-weight: 700;">Pelatih Utama</p>
                <div class="sig-space"></div>
                <div class="sig-name">( Sabeum Lukman )</div>
                <p class="sig-role">Pelatih Kepala Eagle TKD</p>
            </td>
            <td>
                <p style="margin: 0;">Tanjungpinang, {{ date('d F Y') }}</p>
                <p style="margin: 2px 0 0 0; font-weight: 700;">Pimpinan / Pengelola Dojang</p>
                <div class="sig-space"></div>
                <div class="sig-name">( Superadmin / Mingle )</div>
                <p class="sig-role">Owner &amp; Pimpinan Club</p>
            </td>
        </tr>
    </table>

</body>
</html>
