@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="space-y-4">

    <!-- Grand Master Header Card -->
    <div class="bg-gradient-to-br from-red-950/60 via-slate-900 to-slate-900 border border-red-900/50 rounded-3xl p-5 shadow-xl relative overflow-hidden">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-red-600 via-amber-500 to-yellow-400 p-0.5 shadow-md shrink-0">
                <div class="w-full h-full rounded-[14px] bg-slate-900 flex items-center justify-center font-bold text-white text-2xl">
                    👑
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-red-950 text-red-300 border border-red-700/60">
                    Superadmin / Owner
                </span>
                <h1 class="text-base font-extrabold text-white truncate mt-1">{{ $user->name }}</h1>
                <p class="text-xs text-slate-400">
                    Sistem Kontrol Penuh Absensi, Penjadwalan & Penggajian Pelatih
                </p>
            </div>
        </div>

        <!-- 4 Key Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4 pt-3 border-t border-slate-800">
            <div class="bg-slate-950/80 border border-slate-800 p-3 rounded-2xl col-span-2 sm:col-span-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                    Total Honor Bulan Ini ({{ $now->translatedFormat('F Y') }})
                </span>
                <div class="text-xl font-black text-emerald-400 mt-1">
                    Rp {{ number_format($totalEstimatedSalaryThisMonth, 0, ',', '.') }}
                </div>
                <p class="text-[10px] text-slate-500 mt-0.5">
                    Dari {{ $totalHadirThisMonth }} sesi latihan tervalidasi server
                </p>
            </div>

            <div class="bg-slate-950/80 border border-slate-800 p-3 rounded-2xl">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Asisten Pelatih</span>
                <div class="text-xl font-black text-white mt-1">{{ $totalCoaches }}</div>
                <p class="text-[10px] text-blue-400 mt-0.5">+{{ $totalHeadCoaches }} Pelatih Utama</p>
            </div>

            <div class="bg-slate-950/80 border border-slate-800 p-3 rounded-2xl">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Cabang Dojang</span>
                <div class="text-xl font-black text-white mt-1">{{ $totalDojangs }}</div>
                <p class="text-[10px] text-slate-500 mt-0.5">Unit Latihan</p>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Shortcuts -->
    <div class="grid grid-cols-4 gap-2 text-xs">
        <a href="{{ route('schedules.index') }}" class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-red-500/50 flex flex-col items-center justify-center text-center gap-1.5 transition-all group">
            <div class="w-8 h-8 rounded-xl bg-red-600/20 text-red-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="calendar" class="w-4 h-4"></i>
            </div>
            <span class="font-bold text-slate-200">Jadwal</span>
        </a>

        <a href="{{ route('reports.index') }}" class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-red-500/50 flex flex-col items-center justify-center text-center gap-1.5 transition-all group">
            <div class="w-8 h-8 rounded-xl bg-emerald-600/20 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="file-bar-chart" class="w-4 h-4"></i>
            </div>
            <span class="font-bold text-slate-200">Laporan</span>
        </a>

        <a href="{{ route('users.index') }}" class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-red-500/50 flex flex-col items-center justify-center text-center gap-1.5 transition-all group">
            <div class="w-8 h-8 rounded-xl bg-blue-600/20 text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
            <span class="font-bold text-slate-200">Pengguna</span>
        </a>

        <a href="{{ route('activity-logs.index') }}" class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-red-500/50 flex flex-col items-center justify-center text-center gap-1.5 transition-all group">
            <div class="w-8 h-8 rounded-xl bg-amber-600/20 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i data-lucide="shield" class="w-4 h-4"></i>
            </div>
            <span class="font-bold text-slate-200">Log</span>
        </a>
    </div>

    <!-- Recent Activity Logs -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                <i data-lucide="activity" class="w-4 h-4 text-amber-400"></i> Aktivitas Sistem Terkini
            </h2>
            <a href="{{ route('activity-logs.index') }}" class="text-[11px] text-slate-400 hover:text-white">Semua Log &rarr;</a>
        </div>

        <div class="space-y-2">
            @foreach($recentLogs as $log)
                <div class="p-2.5 rounded-2xl bg-slate-950/70 border border-slate-800 flex items-center justify-between text-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[8px] font-mono px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 font-bold">
                                {{ $log->action }}
                            </span>
                            <span class="font-bold text-white">{{ $log->user ? $log->user->name : 'Sistem' }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[240px] sm:max-w-md">{{ $log->description }}</p>
                    </div>
                    <span class="text-[10px] text-slate-500 font-mono shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
