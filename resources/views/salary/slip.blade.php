<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#00288E">
    <title>Slip Gaji - {{ $coach->name }} - {{ $summary['month_name'] }} {{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: 500;
            font-style: normal;
            line-height: 1;
            white-space: nowrap;
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        @media print {
            @page { size: A4; margin: 12mm; }
            body { background: white !important; color: #131B2E !important; }
            .no-print { display: none !important; }
            .slip-shell { max-width: none !important; padding: 0 !important; }
            .slip-card { box-shadow: none !important; border-color: #CBD5E1 !important; }
            .signature-card, .calculation-list, .total-panel { break-inside: avoid; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#F8FAFC] text-[#131B2E] antialiased">
    <main class="slip-shell mx-auto w-full max-w-2xl px-3 py-4 sm:px-4 sm:py-8">
        <div class="no-print mb-3 flex flex-wrap items-center justify-between gap-2">
            <a href="{{ $backUrl }}" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl border border-[#DAE2FD] bg-white px-3 py-2 text-xs font-bold text-[#00288E] shadow-sm transition-colors hover:bg-[#EAEDFF]">
                <span class="material-symbols-rounded text-base">arrow_back</span>
                Kembali
            </a>
            <button type="button" onclick="window.print()" class="inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-[#0051D5] px-3 py-2 text-xs font-bold text-white shadow-sm transition-colors hover:bg-[#003EA8] sm:px-4">
                <span class="material-symbols-rounded text-base">print</span>
                Cetak / Simpan PDF
            </button>
        </div>

        <article class="slip-card overflow-hidden rounded-2xl border border-[#DAE2FD] bg-white shadow-sm">
            <header class="flex items-center gap-3 bg-gradient-to-r from-[#00288E] to-[#0051D5] px-4 py-4 text-white sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <img src="{{ asset('images/icon-eagle.jpg') }}" alt="Logo Eagle Taekwondo" class="h-11 w-11 shrink-0 rounded-xl border border-white/30 object-cover">
                    <div class="min-w-0">
                        <h1 class="truncate text-sm font-extrabold tracking-tight">EAGLE TAEKWONDO CLUB</h1>
                        <p class="mt-0.5 text-[10px] font-medium text-blue-100">Pusat Pembinaan Prestasi Taekwondo Indonesia</p>
                        @if($coach->dojang?->address)
                            <p class="mt-0.5 break-words text-[10px] leading-relaxed text-blue-100/80">{{ $coach->dojang->address }}</p>
                        @endif
                    </div>
                </div>
            </header>

            <div class="space-y-4 p-4 sm:p-6">
                <section class="grid grid-cols-1 gap-3 rounded-xl border border-[#DAE2FD]/70 bg-[#F8FAFC] p-3 sm:grid-cols-2">
                    <div class="min-w-0">
                        <p class="text-[9px] font-semibold uppercase tracking-wide text-[#64748B]">Asisten Pelatih</p>
                        <p class="mt-1 truncate text-sm font-bold text-[#131B2E]">{{ $coach->name }}</p>
                        <p class="mt-0.5 text-[10px] text-[#64748B]">Sabuk: {{ $coach->belt_level ?: '-' }}</p>
                    </div>
                    <div class="min-w-0 sm:text-right">
                        <p class="text-[9px] font-semibold uppercase tracking-wide text-[#64748B]">Periode</p>
                        <p class="mt-1 text-sm font-bold text-[#0051D5]">{{ $summary['month_name'] }} {{ $year }}</p>
                        <p class="mt-0.5 truncate text-[10px] text-[#64748B]">{{ $coach->dojang?->name ?? 'Eagle TKD' }}</p>
                    </div>
                </section>

                <section>
                    <h2 class="mb-2 flex items-center gap-1.5 text-xs font-bold text-[#131B2E]">
                        <span class="material-symbols-rounded text-base text-[#0051D5]">receipt_long</span>
                        Rincian Perhitungan
                    </h2>
                    <div class="calculation-list divide-y divide-[#DAE2FD]/70 overflow-hidden rounded-xl border border-[#DAE2FD]/70">
                        <div class="flex items-center justify-between gap-3 bg-white px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold text-[#131B2E]">Honor kehadiran latihan</p>
                                <p class="mt-0.5 text-[9px] text-[#64748B]">{{ $summary['total_approved_sessions'] }} sesi × Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}</p>
                            </div>
                            <strong class="shrink-0 text-xs font-bold tabular-nums text-[#131B2E]">Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}</strong>
                        </div>

                        @if($summary['estimated_bonus'] > 0)
                            <div class="flex items-center justify-between gap-3 bg-emerald-50/70 px-3 py-2.5">
                                <span class="text-[10px] font-semibold text-[#131B2E]">Bonus / tunjangan</span>
                                <strong class="shrink-0 text-xs font-bold tabular-nums text-emerald-700">+ Rp {{ number_format($summary['estimated_bonus'], 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        @if($summary['estimated_deductions'] > 0)
                            <div class="flex items-center justify-between gap-3 bg-rose-50/70 px-3 py-2.5">
                                <span class="text-[10px] font-semibold text-[#131B2E]">Potongan</span>
                                <strong class="shrink-0 text-xs font-bold tabular-nums text-rose-700">− Rp {{ number_format($summary['estimated_deductions'], 0, ',', '.') }}</strong>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="total-panel flex items-center justify-between gap-3 rounded-xl border border-[#DAE2FD] bg-[#EAEDFF] px-3.5 py-3">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wide text-[#00288E]">Total diterima</p>
                        <p class="text-[9px] text-[#64748B]">Honor bersih periode ini</p>
                    </div>
                    <strong class="shrink-0 text-base font-extrabold tabular-nums text-[#00288E]">Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}</strong>
                </section>

                <section class="grid grid-cols-1 gap-4 pt-4 text-center sm:grid-cols-2">
                    <div class="signature-card px-2 py-3 sm:px-4">
                        <p class="text-xs text-[#64748B]">Mengetahui</p>
                        <p class="mt-1 text-sm font-bold text-[#131B2E]">Pelatih Utama</p>
                        @php
                            $qrCodeRows = [
                                '111111100010101111111',
                                '100000100000101000001',
                                '101110101010001011101',
                                '101110100000101011101',
                                '101110100101101011101',
                                '100000100111001000001',
                                '111111101010101111111',
                                '000000001010000000000',
                                '111011111010100000000',
                                '100110000001010100000',
                                '110010101111011101111',
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
                            $qrCodeModules = [];

                            foreach ($qrCodeRows as $rowIndex => $qrCodeRow) {
                                foreach (str_split($qrCodeRow) as $columnIndex => $module) {
                                    if ($module === '1') {
                                        $qrCodeModules[] = ['x' => $columnIndex + 4, 'y' => $rowIndex + 4];
                                    }
                                }
                            }
                        @endphp
                        <div class="relative flex h-28 items-center justify-center py-1">
                            <svg class="h-24 w-24 shrink-0" viewBox="0 0 29 29" role="img" aria-label="QR code Lukman" shape-rendering="crispEdges">
                                <rect width="29" height="29" fill="#FFFFFF" />
                                @foreach ($qrCodeModules as $qrCodeModule)
                                    <rect x="{{ $qrCodeModule['x'] }}" y="{{ $qrCodeModule['y'] }}" width="1" height="1" fill="#131B2E" />
                                @endforeach
                            </svg>
                            <div class="absolute bottom-2 left-1/2 h-px w-4/5 -translate-x-1/2 bg-[#94A3B8]" aria-hidden="true"></div>
                        </div>
                        <p class="mt-2 text-sm font-bold text-[#131B2E]">Lukman</p>
                        <p class="mt-1 text-xs text-[#64748B]">Dan II Kukkiwon</p>
                    </div>
                    <div class="signature-card px-2 py-3 sm:px-4">
                        <p class="text-xs text-[#64748B]">Diterima</p>
                        <p class="mt-1 text-sm font-bold text-[#131B2E]">Asisten Pelatih</p>
                        <div class="flex h-28 items-end justify-center pb-2" aria-hidden="true">
                            <div class="h-px w-4/5 bg-[#94A3B8]"></div>
                        </div>
                        <p class="mt-2 truncate text-sm font-bold text-[#131B2E]">{{ $coach->name }}</p>
                        <p class="mt-1 text-xs text-[#64748B]">{{ $coach->belt_level ?: '-' }}</p>
                    </div>
                </section>

                <p class="border-t border-[#DAE2FD]/70 pt-4 text-center text-[10px] leading-relaxed text-[#64748B]">
                    Dokumen ini dibuat otomatis oleh Sistem Absensi &amp; Honorarium Eagle Taekwondo.
                </p>
            </div>
        </article>
    </main>
</body>
</html>
