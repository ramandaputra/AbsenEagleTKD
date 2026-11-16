@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Absensi & Piket')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Top Navigation & Action Card -->
    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('dashboard') }}"
               class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#131B2E] hover:bg-[#DAE2FD] flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-[#131B2E] truncate">Laporan &amp; Export Presensi</h1>
                <p class="text-[11px] text-[#64748B] truncate mt-0.5">Rekapitulasi kehadiran &amp; tugas piket pelatih</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('reports.export.excel', request()->all()) }}"
               class="flex-1 sm:flex-none px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-colors">
                <span class="material-symbols-rounded text-base">table_chart</span>
                <span>Excel</span>
            </a>
            <a href="{{ route('reports.export.pdf', request()->all()) }}"
               class="flex-1 sm:flex-none px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-colors">
                <span class="material-symbols-rounded text-base">picture_as_pdf</span>
                <span>PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 text-xs">
            <div>
                <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}"
                       class="w-full py-2 px-2.5 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
            </div>

            <div>
                <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}"
                       class="w-full py-2 px-2.5 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
            </div>

            <div>
                <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Pilih Asisten Pelatih</label>
                <select name="user_id"
                        class="w-full py-2 px-2.5 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                    <option value="">Semua Pelatih</option>
                    @foreach($coachesList as $cl)
                        <option value="{{ $cl->id }}" {{ $selectedUserId == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full py-2 px-3 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-colors">
                    <span class="material-symbols-rounded text-base">filter_alt</span>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 4 Metrics Stats Grid (Matching Tirtafield Style) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
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
    </div>

    <!-- Recap Table per Assistant Coach -->
    <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-[#131B2E] flex items-center gap-1.5">
                <span class="material-symbols-rounded text-[#0051D5] text-lg">analytics</span>
                <span>Rekapitulasi Kehadiran &amp; Honor Pelatih</span>
            </h2>
            <span class="text-[10px] text-[#64748B]">{{ count($coaches) }} Pelatih</span>
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
    </div>

</div>
@endsection
