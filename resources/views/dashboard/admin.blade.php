@extends('layouts.app')

@section('title', 'Dashboard Pelatih')

@section('content')
<div class="space-y-4">

    <!-- Header Card -->
    <div class="bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border border-amber-900/40 rounded-3xl p-5 shadow-lg relative overflow-hidden">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-red-600 p-0.5 shadow-md shrink-0">
                <div class="w-full h-full rounded-[14px] bg-slate-900 flex items-center justify-center font-bold text-white text-2xl">
                    🥋
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-amber-950 text-amber-300 border border-amber-800/60">
                    Admin / Pelatih
                </span>
                <h1 class="text-base font-extrabold text-white truncate mt-1">{{ $user->name }}</h1>
                <p class="text-xs text-slate-400">
                    Sistem Penjadwalan Latihan, Piket Mengajar & Koreksi Absensi
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-slate-800 text-xs">
            <a href="{{ route('schedules.index') }}" class="py-2 px-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold flex items-center justify-center gap-1.5 transition-colors">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                <span>Kelola Jadwal & Piket</span>
            </a>
            <a href="{{ route('reports.index') }}" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold flex items-center justify-center gap-1.5 transition-colors">
                <i data-lucide="file-bar-chart" class="w-4 h-4"></i>
                <span>Laporan & Export</span>
            </a>
        </div>
    </div>

    <!-- Assistant Coaches Recap -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4 text-red-500"></i> Rekap Asisten Pelatih Bulan Ini
                </h2>
                <p class="text-[11px] text-slate-400">Periode {{ $now->translatedFormat('F Y') }}</p>
            </div>
            <a href="{{ route('reports.index') }}" class="text-[11px] font-bold text-red-400 hover:text-red-300">
                Laporan &rarr;
            </a>
        </div>

        <div class="space-y-2.5">
            @forelse($coaches as $coach)
                @php $summary = $coach->monthly_summary; @endphp
                <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-bold text-white">{{ $coach->name }}</p>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">
                                {{ $coach->belt_level }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                            <span>Hadir: <strong class="text-emerald-400">{{ $summary['total_approved_sessions'] }}</strong></span>
                            <span>•</span>
                            <span>Izin: <strong class="text-amber-400">{{ $summary['total_izin_sessions'] }}</strong></span>
                            <span>•</span>
                            <span>Sakit: <strong class="text-blue-400">{{ $summary['total_sakit_sessions'] }}</strong></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-black text-emerald-400">
                            Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
                        </div>
                        <a href="{{ route('salary.admin.slip', ['coach' => $coach->id, 'month' => $currentMonth, 'year' => $currentYear]) }}" class="text-[10px] text-slate-400 hover:text-red-400 flex items-center justify-end gap-1 mt-0.5">
                            <i data-lucide="printer" class="w-3 h-3"></i> Slip
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-500 text-center py-4">Belum ada asisten pelatih.</p>
            @endforelse
        </div>
    </div>

    <!-- Today's Attendance Feed -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-1.5">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-400"></i> Absensi Masuk Hari Ini
        </h2>

        @if($todayAttendances->isEmpty())
            <p class="text-xs text-slate-500 text-center py-4">Belum ada absensi tercatat hari ini.</p>
        @else
            <div class="space-y-2">
                @foreach($todayAttendances as $att)
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden flex items-center justify-center shrink-0">
                                @if($att->photos->isNotEmpty())
                                    <img src="/{{ $att->photos->first()->photo_path }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-white">{{ $att->user->name }}</p>
                                <p class="text-[10px] text-slate-400">
                                    {{ substr($att->time, 0, 5) }} WIB • {{ $att->trainingSchedule ? $att->trainingSchedule->activity_name : 'Latihan' }}
                                </p>
                            </div>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                            {{ $att->status === 'Hadir' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' :
                              ($att->status === 'Izin' ? 'bg-amber-950 text-amber-400 border border-amber-800' : 'bg-blue-950 text-blue-400 border border-blue-800') }}">
                            {{ $att->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
