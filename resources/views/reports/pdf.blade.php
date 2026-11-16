<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kehadiran Pelatih - {{ $start_date }} sd {{ $end_date }}</title>
    <style>
        @page {
            margin: 28px 32px 24px 32px;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #00288E;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .kop-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .kop-title {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15pt;
            font-weight: bold;
            color: #00288E;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-address {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        /* Judul Dokumen */
        .doc-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-period {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #00288E;
            margin: 3px 0 0 0;
            font-weight: bold;
        }

        /* Ringkasan Eksekutif */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            margin-bottom: 12px;
        }
        .summary-table td {
            border: none;
            padding: 7px 12px;
            font-size: 9pt;
        }
        .summary-label {
            font-family: Arial, Helvetica, sans-serif;
            color: #64748b;
            font-size: 8pt;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .summary-val {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            color: #00288E;
            margin-top: 1px;
        }
        .summary-val.emerald {
            color: #15803d;
        }

        /* Tabel Rekapitulasi Data */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .data-table th {
            font-family: Arial, Helvetica, sans-serif;
            background: #F1F5F9;
            color: #334155;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 6px;
            border: 1px solid #CBD5E1;
            text-align: center;
        }
        .data-table td {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            padding: 5px 6px;
            border: 1px solid #CBD5E1;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background: #FAFAFA;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .status-hadir {
            color: #15803d;
            font-weight: bold;
        }
        .date-chip {
            display: inline-block;
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            padding: 1px 4px;
            font-size: 8pt;
            font-weight: bold;
            margin: 1px 1px;
            white-space: nowrap;
        }

        /* Baris Total */
        .total-row td {
            background: #EAEDFF !important;
            font-weight: bold;
            color: #00288E;
            border-top: 2px solid #00288E;
            font-size: 9.5pt;
        }

        /* Catatan Kaki */
        .note-text {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #64748b;
            font-style: italic;
            margin-top: 4px;
            margin-bottom: 18px;
        }

        /* Area Tanda Tangan */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sig-table td {
            border: none;
            vertical-align: top;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
        }
        .sig-name {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            display: inline-block;
            padding-bottom: 2px;
            margin-top: 4px;
        }
        .sig-rank {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            font-weight: bold;
            color: #1e293b;
            margin: 3px 0 0 0;
        }
    </style>
</head>
<body>

    @php
        // Generate QR barcode modules for Sabeum Lukman
        $matrix = [
            '111111100101011111111',
            '100000101010101000001',
            '101110101110001011101',
            '101110100111001011101',
            '101110101101001011101',
            '100000101011001000001',
            '111111101010101111111',
            '000000001101100000000',
            '101111001010101011101',
            '010010110110011100100',
            '110100011011010101101',
            '101110011001110110010',
            '111101101101011100110',
            '000000001000001101110',
            '111111100110100011011',
            '100000100110001110011',
            '101110100000101100111',
            '101110100111010100110',
            '101110100001011111101',
            '100000100111110000010',
            '111111100111011010111',
        ];

        $qrDataUri = null;
        if (function_exists('imagecreatetruecolor')) {
            $size = count($matrix);
            $scale = 4;
            $border = 2;
            $imgSize = ($size + ($border * 2)) * $scale;
            $im = imagecreatetruecolor($imgSize, $imgSize);
            $white = imagecolorallocate($im, 255, 255, 255);
            $dark = imagecolorallocate($im, 19, 27, 46);
            imagefilledrectangle($im, 0, 0, $imgSize, $imgSize, $white);
            for ($r = 0; $r < $size; $r++) {
                for ($c = 0; $c < $size; $c++) {
                    if ($matrix[$r][$c] === '1') {
                        $x1 = ($c + $border) * $scale;
                        $y1 = ($r + $border) * $scale;
                        imagefilledrectangle($im, $x1, $y1, $x1 + $scale - 1, $y1 + $scale - 1, $dark);
                    }
                }
            }
            ob_start();
            imagepng($im);
            $qrDataUri = 'data:image/png;base64,' . base64_encode(ob_get_clean());
            imagedestroy($im);
        }
    @endphp

    <!-- Kop Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 100%;">
                <h1 class="kop-title">EAGLE TAEKWONDO CLUB</h1>
                <p class="kop-address">Jl. Mt Haryono Km.4 Kel. Tanjung Unggat Kec. Bukit Bestari Kota Tanjungpinang Provinsi Kepulauan Riau</p>
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
                <th style="width: 110px;" class="text-center">Nama Asisten Pelatih</th>
                <th style="width: 65px;" class="text-center">Sabuk</th>
                <th style="width: 65px;" class="text-center">Tarif/Sesi</th>
                <th style="width: 55px;" class="text-center">Sesi Hadir</th>
                <th style="width: 80px;" class="text-center">Total Honor</th>
                <th class="text-center">Tanggal Hadir dalam Periode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coaches as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $c['coach']->name }}</td>
                    <td class="text-center">{{ $c['coach']->belt_level ?: 'Pelatih' }}</td>
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
                <td colspan="4" class="text-left font-bold" style="text-align: left;">TOTAL KESELURUHAN:</td>
                <td class="text-center font-bold">{{ $total_hadir }} Sesi</td>
                <td class="text-right font-bold">Rp {{ number_format($total_salary_overall, 0, ',', '.') }}</td>
                <td class="font-bold text-center">{{ $active_coaches_count }} Asisten Pelatih Aktif</td>
            </tr>
        </tbody>
    </table>

    <p class="note-text">
        * Dokumen ini sah dan diterbitkan secara elektronik oleh sistem Absensi Eagle Taekwondo Club. Perhitungan honor didasarkan murni pada presensi Hadir terverifikasi.
    </p>

    <!-- Pengesahan & Tanda Tangan: Hanya Pelatih Utama (Sabeum Lukman) dengan Barcode Persegi -->
    <table class="sig-table">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%; text-align: center;">
                <p style="margin: 0; font-size: 9.5pt; color: #475569;">Tanjungpinang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 2px 0 0 0; font-size: 10pt; font-weight: bold; color: #0f172a;">Mengetahui &amp; Menyetujui,</p>
                <p style="margin: 1px 0 6px 0; font-size: 9pt; font-weight: bold; color: #00288E;">Pelatih Utama Eagle Taekwondo Club</p>

                <!-- Tanda Tangan Barcode Persegi -->
                <div style="margin: 6px auto; text-align: center;">
                    @if($qrDataUri)
                        <img src="{{ $qrDataUri }}" width="72" height="72" style="border: 1px solid #CBD5E1; padding: 2px; background: #ffffff;" alt="QR Barcode Lukman">
                    @else
                        <table style="border-collapse: collapse; margin: 0 auto; width: 72px; height: 72px; background: #fff; border: 1px solid #CBD5E1;">
                            @foreach($matrix as $row)
                                <tr>
                                    @foreach(str_split($row) as $cell)
                                        <td style="padding: 0; width: 3.4px; height: 3.4px; background: {{ $cell === '1' ? '#131B2E' : '#FFFFFF' }}; border: none;"></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    <div style="font-size: 7.5pt; color: #00288E; font-weight: bold; margin-top: 3px; letter-spacing: 0.5px;">E-SIGNATURE VERIFIED</div>
                </div>

                <div style="margin-top: 6px;">
                    <span class="sig-name">Sabeum Lukman</span>
                    <p class="sig-rank">DAN II Kukkiwon</p>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
