@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Absensi & Piket')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Executive Header Banner (Matching Schedules & Salary Style) -->
    <section class="relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] p-4 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-5 -top-5 h-36 w-36 rounded-full bg-blue-500/20 blur-xl"></div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-cyan-400/10 blur-xl"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full border border-blue-300/40 bg-blue-500/25 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                    Laporan &amp; Rekapitulasi
                </span>
                <h1 class="mt-1.5 text-sm font-extrabold tracking-tight">Laporan &amp; Export Presensi</h1>
                <p class="mt-0.5 text-[11px] font-medium text-[#7DD3FC]">Rekapitulasi kehadiran, persentase piket, dan honor pelatih.</p>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <a href="{{ route('reports.export.excel', request()->all()) }}"
                   class="inline-flex h-8 items-center gap-1 rounded-xl bg-emerald-500 hover:bg-emerald-600 px-2.5 text-[10px] font-bold text-white shadow-sm transition-colors">
                    <span class="material-symbols-rounded text-sm">table_chart</span>
                    <span>Excel</span>
                </a>
                <a href="{{ route('reports.export.pdf', request()->all()) }}"
                   class="inline-flex h-8 items-center gap-1 rounded-xl bg-rose-500 hover:bg-rose-600 px-2.5 text-[10px] font-bold text-white shadow-sm transition-colors">
                    <span class="material-symbols-rounded text-sm">picture_as_pdf</span>
                    <span>PDF</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Filter Form Card -->
    <section class="rounded-xl border border-[#DAE2FD]/70 bg-white p-3 shadow-2xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}"
                       class="w-full py-1.5 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}"
                       class="w-full py-1.5 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-[#64748B] mb-1">Asisten Pelatih</label>
                <select name="user_id"
                        class="w-full py-1.5 px-2.5 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
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
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </section>

    <!-- 4 Metrics Stats Grid (Matching Schedules & Summary Style) -->
    <section class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-emerald-700 leading-none">{{ $total_hadir }}</span>
                <span class="material-symbols-rounded text-base text-emerald-600">check_circle</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Total Hadir</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Sesi latihan</div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-amber-700 leading-none">{{ $total_izin }}</span>
                <span class="material-symbols-rounded text-base text-amber-600">event_busy</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Total Izin</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Izin berhalangan</div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-blue-700 leading-none">{{ $total_sakit }}</span>
                <span class="material-symbols-rounded text-base text-blue-600">medical_services</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Total Sakit</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Keterangan sakit</div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[72px]">
            <div class="flex items-center justify-between">
                <span class="text-[19px] font-extrabold text-[#7C3AED] leading-none">{{ $total_duties_overall }}</span>
                <span class="material-symbols-rounded text-base text-[#7C3AED]">assignment_turned_in</span>
            </div>
            <div class="mt-1.5">
                <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Total Piket</div>
                <div class="text-[9.5px] text-[#64748B] truncate">Jadwal penugasan</div>
            </div>
        </div>
    </section>

    <!-- Recap Table per Assistant Coach -->
    <section class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-[#131B2E] flex items-center gap-1.5">
                <span class="material-symbols-rounded text-[#0051D5] text-lg">analytics</span>
                <span>Rekapitulasi Kehadiran &amp; Honor Pelatih</span>
            </h2>
            <span class="text-[10px] font-semibold text-[#0051D5] px-2 py-0.5 rounded-full bg-[#EAEDFF]">
                {{ count($coaches) }} Pelatih
            </span>
        </div>

        <div class="overflow-x-auto no-scrollbar -mx-3.5 px-3.5">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-[#64748B] border-b border-[#DAE2FD] text-[10px] uppercase font-bold bg-[#FAF8FF]">
                        <th class="py-2.5 px-2 text-left rounded-l-lg">Nama Pelatih</th>
                        <th class="py-2.5 px-2 text-center">Piket</th>
                        <th class="py-2.5 px-2 text-center text-emerald-700">Hadir</th>
                        <th class="py-2.5 px-2 text-center text-amber-700">Izin</th>
                        <th class="py-2.5 px-2 text-center text-blue-700">Sakit</th>
                        <th class="py-2.5 px-2 text-center text-rose-700">Alpa</th>
                        <th class="py-2.5 px-2 text-center">% Hadir</th>
                        <th class="py-2.5 px-2 text-right rounded-r-lg">Total Honor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#EAEDFF]">
                    @forelse($coaches as $c)
                        <tr class="hover:bg-[#F2F3FF]/50 transition-colors">
                            <td class="py-2.5 px-2">
                                <strong class="text-[#131B2E] block">{{ $c['coach']->name }}</strong>
                                <span class="text-[10px] text-[#64748B]">{{ $c['coach']->belt_level ?: 'Pelatih' }}</span>
                            </td>
                            <td class="py-2.5 px-2 text-center font-bold text-[#131B2E]">{{ $c['total_duties'] }}</td>
                            <td class="py-2.5 px-2 text-center font-bold text-emerald-700">{{ $c['hadir_count'] }}</td>
                            <td class="py-2.5 px-2 text-center text-amber-700">{{ $c['izin_count'] }}</td>
                            <td class="py-2.5 px-2 text-center text-blue-700">{{ $c['sakit_count'] }}</td>
                            <td class="py-2.5 px-2 text-center text-rose-700">{{ $c['alpa_count'] }}</td>
                            <td class="py-2.5 px-2 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $c['attendance_rate'] >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $c['attendance_rate'] }}%
                                </span>
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($c['total_salary'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[#64748B]">
                                <span class="material-symbols-rounded text-3xl text-slate-300">bar_chart</span>
                                <p class="text-xs font-semibold text-[#131B2E] mt-1">Tidak Ada Data Laporan</p>
                                <p class="text-[10.5px] text-[#64748B] mt-0.5">Sesuaikan rentang tanggal pada filter di atas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
