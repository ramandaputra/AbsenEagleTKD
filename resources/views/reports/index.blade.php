@extends('layouts.app')

@section('title', 'Laporan Rekapitulasi Absensi & Piket')

@section('content')
<div class="space-y-4">

    <!-- Header & Download Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="file-bar-chart" class="w-5 h-5 text-red-500"></i> Laporan Absensi & Penjadwalan
            </h1>
            <p class="text-xs text-slate-400">Rekapitulasi persentase kehadiran dan tugas piket mengajar</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export.excel', request()->all()) }}" class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md transition-colors">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Unduh Excel</span>
            </a>
            <a href="{{ route('reports.export.pdf', request()->all()) }}" class="flex-1 sm:flex-none px-3.5 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md transition-colors">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Unduh PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <label class="block text-slate-400 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $start_date }}" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $end_date }}" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
            </div>

            <div>
                <label class="block text-slate-400 mb-1">Pilih Asisten Pelatih</label>
                <select name="user_id" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    <option value="">Semua Pelatih</option>
                    @foreach($coachesList as $cl)
                        <option value="{{ $cl->id }}" {{ $selectedUserId == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-1">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold transition-colors">
                    Filter Laporan
                </button>
            </div>
        </form>
    </div>

    <!-- 4 Metrics Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="bg-slate-900 border border-slate-800 p-3.5 rounded-2xl">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Hadir</span>
            <div class="text-xl font-black text-emerald-400 mt-1">{{ $total_hadir }}</div>
            <p class="text-[10px] text-slate-500">Sesi Latihan</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-3.5 rounded-2xl">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Izin</span>
            <div class="text-xl font-black text-amber-400 mt-1">{{ $total_izin }}</div>
            <p class="text-[10px] text-slate-500">Izin Berhalangan</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-3.5 rounded-2xl">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Sakit</span>
            <div class="text-xl font-black text-blue-400 mt-1">{{ $total_sakit }}</div>
            <p class="text-[10px] text-slate-500">Keterangan Sakit</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 p-3.5 rounded-2xl">
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Total Tugas Piket</span>
            <div class="text-xl font-black text-white mt-1">{{ $total_duties_overall }}</div>
            <p class="text-[10px] text-slate-500">Jadwal Ditugaskan</p>
        </div>
    </div>

    <!-- Recap Table per Assistant Coach -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg overflow-x-auto no-scrollbar">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-1.5">
            <i data-lucide="percent" class="w-4 h-4 text-emerald-400"></i> Persentase Kehadiran & Rekap Tugas Mengajar
        </h2>

        <table class="w-full text-xs">
            <thead>
                <tr class="text-slate-400 border-b border-slate-800 text-[10px] uppercase">
                    <th class="py-2 px-2 text-left">Nama Pelatih</th>
                    <th class="py-2 px-2 text-center">Tugas Piket</th>
                    <th class="py-2 px-2 text-center text-emerald-400">Hadir</th>
                    <th class="py-2 px-2 text-center text-amber-400">Izin</th>
                    <th class="py-2 px-2 text-center text-blue-400">Sakit</th>
                    <th class="py-2 px-2 text-center text-rose-400">Alpa</th>
                    <th class="py-2 px-2 text-center font-bold text-white">% Hadir</th>
                    <th class="py-2 px-2 text-right">Total Honor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @foreach($coaches as $c)
                    <tr>
                        <td class="py-2.5 px-2">
                            <strong class="text-white block">{{ $c['coach']->name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ $c['coach']->belt_level }}</span>
                        </td>
                        <td class="py-2.5 px-2 text-center font-bold text-slate-200">{{ $c['total_duties'] }}</td>
                        <td class="py-2.5 px-2 text-center font-bold text-emerald-400">{{ $c['hadir_count'] }}</td>
                        <td class="py-2.5 px-2 text-center text-amber-400">{{ $c['izin_count'] }}</td>
                        <td class="py-2.5 px-2 text-center text-blue-400">{{ $c['sakit_count'] }}</td>
                        <td class="py-2.5 px-2 text-center text-rose-400">{{ $c['alpa_count'] }}</td>
                        <td class="py-2.5 px-2 text-center">
                            <span class="px-2 py-0.5 rounded-full font-bold {{ $c['attendance_rate'] >= 80 ? 'bg-emerald-950 text-emerald-400' : 'bg-amber-950 text-amber-400' }}">
                                {{ $c['attendance_rate'] }}%
                            </span>
                        </td>
                        <td class="py-2.5 px-2 text-right font-mono font-bold text-emerald-400">
                            Rp {{ number_format($c['total_salary'], 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection

