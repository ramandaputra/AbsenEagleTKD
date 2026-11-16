@extends('layouts.app')

@section('title', 'Perhitungan Honor & Gaji')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Top Navigation Card -->
    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('dashboard') }}"
               class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#131B2E] hover:bg-[#DAE2FD] flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-[#131B2E] truncate">Rekapitulasi Honor &amp; Gaji</h1>
                <p class="text-[11px] text-[#64748B] truncate mt-0.5">Penghasilan otomatis berdasarkan presensi</p>
            </div>
        </div>

        <!-- Month & Year Selector -->
        <form method="GET" action="{{ route('salary.index') }}" class="flex items-center gap-1.5 shrink-0">
            <select name="month" onchange="this.form.submit()" class="py-1.5 px-2 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] text-xs font-semibold focus:outline-none focus:border-[#0051D5]">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="py-1.5 px-2 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] text-xs font-semibold focus:outline-none focus:border-[#0051D5]">
                @foreach(range(2025, 2027) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Main Salary Summary Banner (Gradient Tirtafield) -->
    <div class="rounded-2xl p-4 text-white shadow-lg relative overflow-hidden bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] border border-white/15">
        <div class="absolute -right-5 -top-5 w-32 h-32 rounded-full bg-blue-500/20 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-200">
                Honor Periode {{ $summary['month_name'] }} {{ $summary['year'] }}
            </span>
            @if($summary['payroll_record'])
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $summary['payroll_record']->payment_status->badgeClass() }}">
                    {{ $summary['payroll_record']->payment_status->label() }}
                </span>
            @else
                <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-white/10 text-white/80 border border-white/20">
                    Draft Berjalan
                </span>
            @endif
        </div>

        <div class="relative z-10 my-3">
            <div class="text-3xl font-black text-emerald-400 tracking-tight font-mono">
                Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-white/70 mt-1">
                Total penghasilan bersih (Take Home Pay)
            </p>
        </div>

        <!-- Formula Breakdown Grid -->
        <div class="relative z-10 grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-white/15 text-xs">
            <div class="bg-white/10 border border-white/15 p-2.5 rounded-xl">
                <span class="text-[10px] text-white/70 font-semibold block">Total Kehadiran</span>
                <span class="text-base font-black text-white font-mono">{{ $summary['total_approved_sessions'] }} Sesi</span>
                <p class="text-[9.5px] text-emerald-300 mt-0.5">Tervalidasi Hadir</p>
            </div>

            <div class="bg-white/10 border border-white/15 p-2.5 rounded-xl">
                <span class="text-[10px] text-white/70 font-semibold block">Tarif per Kehadiran</span>
                <span class="text-base font-black text-white font-mono">Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}</span>
                <p class="text-[9.5px] text-white/70 mt-0.5">Sabuk {{ $user->belt_level ?: 'Pelatih' }}</p>
            </div>
        </div>

        <!-- Print Slip Button -->
        <div class="relative z-10 mt-3.5 pt-3 border-t border-white/15 flex items-center justify-between">
            <div class="text-[10.5px] text-white/80">
                Rekening: <strong class="text-white">{{ $user->bank_name ?: 'BCA' }} {{ $user->bank_account_number ?: '-' }}</strong>
            </div>
            <a href="{{ route('salary.slip', ['month' => $month, 'year' => $year]) }}"
               class="py-1.5 px-3 rounded-xl bg-white text-[#00288E] hover:bg-[#EAEDFF] font-bold text-xs flex items-center gap-1.5 shadow-xs transition-colors">
                <span class="material-symbols-rounded text-base">print</span>
                <span>Cetak Slip</span>
            </a>
        </div>
    </div>

    <!-- Attendance Breakdown (Rincian Kehadiran) -->
    <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-3">
        <h2 class="text-xs font-bold text-[#131B2E] flex items-center gap-1.5">
            <span class="material-symbols-rounded text-emerald-600 text-lg">fact_check</span>
            <span>Rincian Presensi yang Dihitung Honor</span>
        </h2>

        @if($summary['attendances']->where('status', \App\Enums\AttendanceStatus::Approved)->isEmpty())
            <div class="p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">event_busy</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Sesi Disetujui</p>
                <p class="text-[10.5px] text-[#64748B] mt-0.5">Presensi latihan bulan ini belum tercatat.</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($summary['attendances']->where('status', \App\Enums\AttendanceStatus::Approved) as $index => $att)
                    <div class="p-3 rounded-xl bg-[#FAF8FF] border border-[#EAEDFF] flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-lg bg-[#EAEDFF] text-[#0051D5] font-bold flex items-center justify-center text-[10px]">
                                {{ $loop->iteration }}
                            </span>
                            <div>
                                <p class="font-bold text-[#131B2E]">{{ $att->date->translatedFormat('l, d F Y') }}</p>
                                <p class="text-[10px] text-[#64748B]">
                                    {{ $att->trainingSession ? $att->trainingSession->name : ($att->dojang?->name ?? 'Dojang Pusat') }}
                                    ({{ substr($att->check_in_time, 0, 5) }} WIB)
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-emerald-700">
                                + Rp {{ number_format($att->session_rate, 0, ',', '.') }}
                            </span>
                            <p class="text-[9.5px] text-emerald-600 font-semibold">Disetujui</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
