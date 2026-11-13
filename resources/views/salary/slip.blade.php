<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $coach->name }} - {{ $summary['month_name'] }} {{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono-slip { font-family: 'Courier Prime', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; color: black !important; }
            .slip-card { box-shadow: none !important; border: 1px solid #000 !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-900 min-h-screen py-6 px-4 flex flex-col items-center justify-center">

    <!-- Top Action Bar (No Print) -->
    <div class="w-full max-w-2xl mb-4 flex items-center justify-between no-print">
        <a href="{{ url()->previous() ?: route('salary.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 text-xs font-bold hover:bg-slate-700 transition-colors flex items-center gap-2">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold shadow-lg transition-colors flex items-center gap-2">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Official Slip Paper -->
    <div class="slip-card w-full max-w-2xl bg-white text-slate-900 rounded-3xl p-8 shadow-2xl border border-slate-200 relative overflow-hidden">
        
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none select-none text-9xl font-black">
            🦅
        </div>

        <!-- Header Kop Surat -->
        <div class="flex items-center justify-between border-b-2 border-red-600 pb-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 flex items-center justify-center text-3xl shadow-md">
                    🦅
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900 leading-none">
                        EAGLE TAEKWONDO CLUB
                    </h1>
                    <p class="text-xs text-slate-600 font-semibold mt-1">
                        Pusat Pembinaan Prestasi Taekwondo Indonesia
                    </p>
                    <p class="text-[11px] text-slate-500">
                        GOR Pajajaran - Bogor | Telp: 0812-3456-7890
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 rounded-md bg-red-100 text-red-800 text-xs font-black uppercase tracking-wider border border-red-200">
                    SLIP HONORARIUM
                </span>
                <p class="text-[11px] text-slate-500 mt-1 font-mono">
                    No: SLIP/{{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $coach->id }}
                </p>
            </div>
        </div>

        <!-- Coach Information -->
        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs mb-6">
            <div>
                <p class="text-slate-500 font-medium">Nama Asisten Pelatih:</p>
                <p class="text-sm font-black text-slate-900">{{ $coach->name }}</p>
                <p class="text-[11px] text-slate-600 mt-0.5">Tingkat Sabuk: <strong>{{ $coach->belt_level ?: '-' }}</strong></p>
            </div>
            <div class="text-right sm:text-left">
                <p class="text-slate-500 font-medium">Periode Pembayaran:</p>
                <p class="text-sm font-black text-red-600">{{ $summary['month_name'] }} {{ $year }}</p>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Rekening: <strong>{{ $coach->bank_name ?: 'BCA' }} - {{ $coach->bank_account_number ?: '-' }}</strong>
                </p>
            </div>
        </div>

        <!-- Calculation Table -->
        <div class="mb-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Rincian Perhitungan Kehadiran & Honor
            </h2>
            <table class="w-full text-xs border border-slate-200 rounded-xl overflow-hidden">
                <thead class="bg-slate-100 border-b border-slate-200 text-slate-700 font-bold">
                    <tr>
                        <th class="py-2.5 px-3 text-left">Komponen</th>
                        <th class="py-2.5 px-3 text-center">Volume / Kuantitas</th>
                        <th class="py-2.5 px-3 text-right">Tarif Satuan</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-800">
                    <tr>
                        <td class="py-3 px-3">
                            <strong class="text-slate-900">Honor Kehadiran Sesi Latihan</strong>
                            <p class="text-[10px] text-slate-500">Sesi tervalidasi di Dojang {{ $coach->dojang ? $coach->dojang->name : 'Eagle TKD' }}</p>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-900">
                            {{ $summary['total_approved_sessions'] }} Kehadiran
                        </td>
                        <td class="py-3 px-3 text-right font-mono">
                            Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($summary['estimated_bonus'] > 0)
                        <tr class="bg-emerald-50/50">
                            <td class="py-2.5 px-3">
                                <strong>Tunjangan / Bonus Tambahan</strong>
                                <p class="text-[10px] text-slate-500">Bonus kehadiran disiplin / event khusus</p>
                            </td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                + Rp {{ number_format($summary['estimated_bonus'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if($summary['estimated_deductions'] > 0)
                        <tr class="bg-rose-50/50">
                            <td class="py-2.5 px-3">
                                <strong>Potongan</strong>
                            </td>
                            <td class="py-2.5 px-3 text-center">-</td>
                            <td class="py-2.5 px-3 text-right">-</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700">
                                - Rp {{ number_format($summary['estimated_deductions'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="3" class="py-3 px-3 font-extrabold text-sm text-slate-900 uppercase">
                            TOTAL DITERIMA (TAKE HOME PAY)
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-black text-base text-red-600">
                            Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Status Note -->
        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-xs mb-8 flex items-center justify-between">
            <div>
                <span class="text-slate-500">Status Pembayaran:</span>
                <strong class="ml-1 text-slate-900 uppercase">
                    {{ $summary['payroll_record'] ? $summary['payroll_record']->payment_status->label() : 'DRAFT PERHITUNGAN' }}
                </strong>
            </div>
            @if($summary['payroll_record'] && $summary['payroll_record']->payment_status->value === 'paid')
                <div class="text-[11px] font-bold text-emerald-700">
                    ✓ LUNAS DIBAYARKAN ({{ $summary['payroll_record']->payment_date ? $summary['payroll_record']->payment_date->format('d/m/Y') : date('d/m/Y') }})
                </div>
            @endif
        </div>

        <!-- Signatures (3 Kolom: Superadmin, Pelatih Utama, Penerima) -->
        <div class="grid grid-cols-3 gap-4 text-center text-xs pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 text-[10px]">Mengetahui,</p>
                <p class="font-bold text-slate-800">Ketua / Owner Dojang</p>
                <div class="h-16 flex items-center justify-center">
                    <span class="font-mono text-xs text-red-700 font-bold border border-red-300 px-2 py-0.5 rounded rotate-[-5deg]">
                        [ APPROVED ]
                    </span>
                </div>
                <p class="font-black text-slate-900">Grand Master Bambang</p>
                <p class="text-[10px] text-slate-500">Dan 7 Kukkiwon</p>
            </div>

            <div>
                <p class="text-slate-500 text-[10px]">Diverifikasi oleh,</p>
                <p class="font-bold text-slate-800">Pelatih Utama</p>
                <div class="h-16 flex items-center justify-center">
                    <span class="font-mono text-xs text-slate-400">Ttd Digital</span>
                </div>
                <p class="font-black text-slate-900">Sabeum Nim Dimas W.</p>
                <p class="text-[10px] text-slate-500">Dan 4 Kukkiwon</p>
            </div>

            <div>
                <p class="text-slate-500 text-[10px]">Diterima oleh,</p>
                <p class="font-bold text-slate-800">Asisten Pelatih</p>
                <div class="h-16 flex items-center justify-center">
                    <span class="font-mono text-xs text-slate-400">Penerima</span>
                </div>
                <p class="font-black text-slate-900">{{ $coach->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $coach->belt_level }}</p>
            </div>
        </div>

        <div class="text-center text-[10px] text-slate-400 mt-6 pt-3 border-t border-slate-100">
            Dokumen ini dihasilkan secara otomatis oleh Sistem Absensi & Honorarium Eagle Taekwondo.
        </div>
    </div>
</body>
</html>

