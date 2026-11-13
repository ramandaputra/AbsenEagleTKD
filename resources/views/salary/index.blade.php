@extends('layouts.app')

@section('title', 'Perhitungan Honor & Gaji')

@section('content')
<div class="space-y-4">

    <!-- Header & Period Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="wallet" class="w-5 h-5 text-red-500"></i> Rekapitulasi Honor & Gaji
            </h1>
            <p class="text-xs text-slate-400">
                Perhitungan otomatis berdasarkan kehadiran sesi latihan
            </p>
        </div>

        <!-- Month & Year Selector -->
        <form method="GET" action="{{ route('salary.index') }}" class="flex items-center gap-2">
            <select name="month" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-red-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-semibold focus:outline-none focus:border-red-500">
                @foreach(range(2025, 2027) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Salary Summary Card -->
    <div class="bg-gradient-to-br from-red-950/70 via-slate-900 to-slate-900 border border-red-900/50 rounded-3xl p-5 shadow-2xl relative overflow-hidden">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">
                Total Honor Bulan {{ $summary['month_name'] }} {{ $summary['year'] }}
            </span>
            @if($summary['payroll_record'])
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $summary['payroll_record']->payment_status->badgeClass() }}">
                    {{ $summary['payroll_record']->payment_status->label() }}
                </span>
            @else
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-slate-800 text-slate-400 border border-slate-700">
                    Draft Berjalan
                </span>
            @endif
        </div>

        <!-- Big Total Salary Display -->
        <div class="my-3">
            <div class="text-3xl font-black text-emerald-400 tracking-tight">
                Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Total penghasilan bersih (Take Home Pay)
            </p>
        </div>

        <!-- Formula Breakdown Grid -->
        <div class="grid grid-cols-2 gap-2.5 mt-4 pt-3 border-t border-slate-800 text-xs">
            <!-- Jumlah Kehadiran -->
            <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                <span class="text-[10px] text-slate-400 font-semibold block">Total Kehadiran</span>
                <span class="text-lg font-black text-white">{{ $summary['total_approved_sessions'] }} Sesi</span>
                <p class="text-[10px] text-emerald-400 mt-0.5">Tervalidasi Hadir</p>
            </div>

            <!-- Honor per Sesi -->
            <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                <span class="text-[10px] text-slate-400 font-semibold block">Tarif per Kehadiran</span>
                <span class="text-lg font-black text-white">Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-400 mt-0.5">Sesuai Sabuk {{ $user->belt_level }}</p>
            </div>

            <!-- Gaji Pokok -->
            <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                <span class="text-[10px] text-slate-400 font-semibold block">Subtotal Honor Pokok</span>
                <span class="text-sm font-bold text-slate-200">Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}</span>
                <p class="text-[10px] text-slate-500 mt-0.5">{{ $summary['total_approved_sessions'] }} x Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}</p>
            </div>

            <!-- Bonus / Potongan -->
            <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl">
                <span class="text-[10px] text-slate-400 font-semibold block">Tunjangan / Bonus</span>
                <span class="text-sm font-bold text-emerald-400">+ Rp {{ number_format($summary['estimated_bonus'], 0, ',', '.') }}</span>
                @if($summary['estimated_deductions'] > 0)
                    <p class="text-[10px] text-rose-400 mt-0.5">- Rp {{ number_format($summary['estimated_deductions'], 0, ',', '.') }}</p>
                @else
                    <p class="text-[10px] text-slate-500 mt-0.5">Potongan: Rp 0</p>
                @endif
            </div>
        </div>

        <!-- Print Slip Button -->
        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
            <div class="text-[11px] text-slate-400">
                Rekening: <strong class="text-slate-300">{{ $user->bank_name ?: 'BCA' }} {{ $user->bank_account_number ?: '-' }}</strong>
            </div>
            <a href="{{ route('salary.slip', ['month' => $month, 'year' => $year]) }}" class="py-2 px-3.5 rounded-xl bg-gradient-to-r from-red-600 to-amber-600 hover:opacity-95 text-white font-bold text-xs flex items-center gap-1.5 shadow-md">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Slip Gaji</span>
            </a>
        </div>
    </div>

    <!-- Payment Status Detail (If Recorded) -->
    @if($summary['payroll_record'] && $summary['payroll_record']->payment_status->value === 'paid')
        <div class="p-4 rounded-3xl bg-emerald-950/50 border border-emerald-600/40 text-xs">
            <div class="flex items-center gap-2 font-bold text-emerald-300 mb-1">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                Honor Periode Ini Telah Dibayarkan / Lunas
            </div>
            <p class="text-emerald-200/90">
                Dibayarkan pada: <strong>{{ $summary['payroll_record']->payment_date ? $summary['payroll_record']->payment_date->translatedFormat('d F Y') : '-' }}</strong> 
                melalui metode <strong>{{ $summary['payroll_record']->payment_method->label() }}</strong>.
            </p>
            @if($summary['payroll_record']->payment_notes)
                <p class="text-slate-300 mt-1 italic">
                    Catatan: "{{ $summary['payroll_record']->payment_notes }}"
                </p>
            @endif
        </div>
    @endif

    <!-- Attendance Breakdown (Rincian Kehadiran yang Dihitung) -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-1.5">
            <i data-lucide="list-checks" class="w-4 h-4 text-emerald-400"></i> Rincian Tanggal Kehadiran Masuk Gaji
        </h2>

        @if($summary['attendances']->where('status', \App\Enums\AttendanceStatus::Approved)->isEmpty())
            <p class="text-xs text-slate-500 text-center py-4">Belum ada sesi latihan yang disetujui pada bulan ini.</p>
        @else
            <div class="space-y-2">
                @foreach($summary['attendances']->where('status', \App\Enums\AttendanceStatus::Approved) as $index => $att)
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg bg-emerald-950 border border-emerald-800 text-emerald-400 font-bold flex items-center justify-center text-[10px]">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-bold text-white">{{ $att->date->translatedFormat('l, d F Y') }}</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ $att->trainingSession ? $att->trainingSession->name : $att->dojang->name }}
                                    ({{ substr($att->check_in_time, 0, 5) }} WIB)
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-emerald-400">
                                + Rp {{ number_format($att->session_rate, 0, ',', '.') }}
                            </span>
                            <p class="text-[10px] text-slate-500">Disetujui</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

