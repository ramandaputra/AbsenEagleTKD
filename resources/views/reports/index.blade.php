@extends('layouts.app')

@section('title', 'Gantt Chart & Rekap Kehadiran Pelatih')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Executive Header Banner (Matching Schedules & Salary Style) -->
    <section class="relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] p-4 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-5 -top-5 h-36 w-36 rounded-full bg-blue-500/20 blur-xl"></div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-cyan-400/10 blur-xl"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full border border-blue-300/40 bg-blue-500/25 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                    Laporan &amp; Rekapitulasi Hadir
                </span>
                <h1 class="mt-1.5 text-sm font-extrabold tracking-tight">Gantt Chart &amp; Rekap Kehadiran</h1>
                <p class="mt-0.5 text-[11px] font-medium text-[#7DD3FC]">Linimasa tanggal kehadiran asisten pelatih dalam bulan ini.</p>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <a href="{{ route('reports.export.pdf', request()->all()) }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 px-3.5 text-xs font-bold text-white shadow-md transition-colors">
                    <span class="material-symbols-rounded text-base">picture_as_pdf</span>
                    <span>Cetak PDF</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Filter Form Card (Bulan, Tahun & Pelatih) -->
    <section class="rounded-xl border border-[#DAE2FD]/70 bg-white p-3 shadow-2xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Bulan</label>
                <select name="month" class="w-full py-2 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Tahun</label>
                <select name="year" class="w-full py-2 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
                    @foreach(range(2025, 2027) as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Asisten Pelatih</label>
                <select name="user_id"
                        class="w-full py-2 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
                    <option value="">Semua Pelatih</option>
                    @foreach($coachesList as $cl)
                        <option value="{{ $cl->id }}" {{ $selectedUserId == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full py-2 px-3 rounded-lg bg-[#0051D5] hover:bg-[#00288E] text-white font-bold text-xs flex items-center justify-center gap-1 shadow-xs transition-colors">
                    <span class="material-symbols-rounded text-base">filter_alt</span>
                    <span>Tampilkan</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Ringkasan Statistik (Hanya Menghitung Hadir) -->
    <section class="grid grid-cols-3 gap-2">
        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-emerald-700 leading-none">{{ $total_hadir }}</span>
                <span class="material-symbols-rounded text-base text-emerald-600">check_circle</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Total Sesi Hadir</div>
                <div class="text-[9.5px] text-[#64748B] truncate">{{ $month_name }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-[#0051D5] leading-none">{{ $active_coaches_count }}</span>
                <span class="material-symbols-rounded text-base text-[#0051D5]">groups</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Pelatih Aktif Hadir</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Dari {{ count($coaches) }} terdaftar</div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[14px] sm:text-[16px] font-extrabold font-mono text-emerald-700 leading-none">
                    Rp {{ number_format($total_salary_overall, 0, ',', '.') }}
                </span>
                <span class="material-symbols-rounded text-base text-emerald-600">payments</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Akumulasi Honor</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Total terhitung hadir</div>
            </div>
        </div>
    </section>

    <!-- GANTT CHART KEHADIRAN (Tanggal Hadir Bulan Ini) -->
    <section class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-3">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-[#131B2E] flex items-center gap-1.5">
                    <span class="material-symbols-rounded text-[#0051D5] text-lg">calendar_view_month</span>
                    <span>Gantt Chart Kehadiran ({{ $month_name }})</span>
                </h2>
                <p class="text-[10px] text-[#64748B] mt-0.5">Blok hijau menandakan tanggal asisten pelatih hadir di sesi latihan.</p>
            </div>

            <!-- Legend Gantt Chart -->
            <div class="flex items-center gap-3 text-[10px] text-[#64748B]">
                <div class="flex items-center gap-1">
                    <span class="w-3.5 h-3.5 rounded bg-emerald-500 flex items-center justify-center text-white text-[8px] font-bold">✓</span>
                    <span>Hadir</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                    <span>Tidak Hadir</span>
                </div>
            </div>
        </div>

        <!-- Scrollable Gantt Chart Table -->
        <div class="overflow-x-auto no-scrollbar -mx-3.5 px-3.5 border-t border-b border-[#DAE2FD]">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-[#FAF8FF] border-b border-[#DAE2FD]">
                        <!-- Sticky Pelatih Header -->
                        <th class="py-2.5 px-3 text-left sticky left-0 z-20 bg-[#FAF8FF] border-r border-[#DAE2FD] min-w-[130px] font-bold text-[#131B2E]">
                            Asisten Pelatih
                        </th>

                        <!-- Day Columns Header -->
                        @foreach($period_dates as $d)
                            <th class="py-1.5 px-1 text-center min-w-[34px] border-r border-[#EAEDFF] {{ $d['is_today'] ? 'bg-blue-50/80' : ($d['is_weekend'] ? 'bg-amber-50/40' : '') }}">
                                <span class="block text-[8.5px] font-bold uppercase {{ $d['is_today'] ? 'text-[#0051D5]' : ($d['is_weekend'] ? 'text-amber-700' : 'text-[#64748B]') }}">
                                    {{ substr($d['day_name'], 0, 2) }}
                                </span>
                                <span class="block text-[11px] font-mono font-extrabold {{ $d['is_today'] ? 'text-[#0051D5]' : ($d['is_weekend'] ? 'text-amber-900' : 'text-[#131B2E]') }}">
                                    {{ sprintf('%02d', $d['day']) }}
                                </span>
                            </th>
                        @endforeach

                        <!-- Total Hadir & Honor Header -->
                        <th class="py-2.5 px-2.5 text-center min-w-[65px] font-bold text-[#131B2E] border-r border-[#EAEDFF]">
                            Hadir
                        </th>
                        <th class="py-2.5 px-3 text-right min-w-[90px] font-bold text-[#131B2E]">
                            Honor
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EAEDFF]">
                    @forelse($coaches as $c)
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <!-- Sticky Coach Info Column -->
                            <td class="py-2.5 px-3 sticky left-0 z-10 bg-white border-r border-[#DAE2FD] min-w-[130px]">
                                <span class="block font-bold text-[#131B2E] text-xs truncate max-w-[120px]">{{ $c['coach']->name }}</span>
                                <span class="block text-[9.5px] text-[#64748B] truncate">{{ $c['coach']->belt_level ?: 'Pelatih' }}</span>
                            </td>

                            <!-- Gantt Presence Day Cells -->
                            @foreach($period_dates as $d)
                                @php
                                    $isHadir = isset($c['attended_dates'][$d['date']]);
                                @endphp
                                <td class="py-1.5 px-0.5 text-center border-r border-[#EAEDFF] {{ $d['is_today'] ? 'bg-blue-50/40' : ($d['is_weekend'] ? 'bg-amber-50/20' : '') }}">
                                    @if($isHadir)
                                        <div class="w-6 h-6 rounded-md bg-emerald-500 text-white font-bold flex items-center justify-center shadow-2xs mx-auto"
                                             title="{{ $c['coach']->name }} Hadir pada {{ $d['day'] }} {{ $month_name }}">
                                            <span class="material-symbols-rounded text-sm">check</span>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 flex items-center justify-center mx-auto">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-200"></span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach

                            <!-- Summary Hadir & Honor Columns -->
                            <td class="py-2.5 px-2 text-center border-r border-[#EAEDFF]">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $c['hadir_count'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $c['hadir_count'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-xs text-emerald-700 whitespace-nowrap">
                                Rp {{ number_format($c['total_salary'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($period_dates) + 3 }}" class="py-8 text-center text-[#64748B]">
                                <span class="material-symbols-rounded text-3xl text-slate-300">calendar_month</span>
                                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Pelatih Terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Rincian Tanggal Hadir per Asisten Pelatih (Cards List) -->
    <section class="space-y-2.5">
        <div class="px-0.5">
            <h2 class="text-xs sm:text-sm font-bold text-[#131B2E]">Rincian Tanggal Hadir Pelatih</h2>
            <p class="text-[10px] text-[#64748B] mt-0.5">Daftar tanggal spesifik kehadiran asisten pelatih dalam bulan {{ $month_name }}.</p>
        </div>

        @forelse($coaches as $c)
            <article class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-2.5">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center font-bold text-xs shrink-0">
                            <span class="material-symbols-rounded text-xl">sports_martial_arts</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-xs sm:text-sm font-bold text-[#131B2E] truncate">{{ $c['coach']->name }}</h3>
                            <p class="text-[10px] text-[#64748B] truncate mt-0.5">
                                {{ $c['coach']->belt_level ?: 'Pelatih' }} &bull; Rp {{ number_format($c['coach']->rate_per_session, 0, ',', '.') }}/sesi
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $c['hadir_count'] }} Sesi Hadir
                        </span>
                        <div class="text-xs font-mono font-bold text-emerald-700 mt-1">
                            Rp {{ number_format($c['total_salary'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Chips Tanggal Hadir -->
                <div class="pt-2 border-t border-[#EAEDFF]">
                    <span class="block text-[10px] font-semibold text-[#64748B] mb-1.5">Tanggal Hadir:</span>
                    @if(!empty($c['attended_days_list']))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($c['attended_days_list'] as $dateStr)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono text-[10px] font-bold">
                                    <span class="material-symbols-rounded text-xs text-emerald-600">check</span>
                                    <span>{{ date('d F', strtotime($dateStr)) }}</span>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[10.5px] italic text-[#94A3B8]">Belum ada catatan hadir pada periode bulan ini.</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">person_off</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Tidak Ada Data Pelatih</p>
            </div>
        @endforelse
    </section>

</div>
@endsection
