@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div
    x-data="{
        activePanel: 'attendance',
        searchTerm: '',
        matchesAttendance(name, activity, status) {
            const query = this.searchTerm.trim().toLowerCase();
            return !query || (name + ' ' + activity + ' ' + status).includes(query);
        }
    }"
    class="space-y-3.5"
>
    <!-- Executive Header Banner (Matching Admin/Pelatih Style) -->
    <section class="rounded-2xl p-4 text-white shadow-lg relative overflow-hidden bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] border border-white/15">
        <div class="absolute -right-5 -top-5 w-36 h-36 rounded-full bg-blue-500/20 blur-xl pointer-events-none"></div>
        <div class="absolute -left-6 -bottom-6 w-32 h-32 rounded-full bg-cyan-400/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/25 text-rose-100 border border-rose-300/40">
                    Dashboard Superadmin
                </span>
                <h1 class="text-sm font-extrabold text-white tracking-tight mt-1.5 leading-snug">
                    Eagle Taekwondo Club
                </h1>
                <p class="text-[11.5px] font-semibold text-[#7DD3FC] mt-0.5">
                    Pusat Kontrol Sistem &amp; Presensi Pelatih
                </p>
                <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-white/80 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="truncate">Halo, {{ $user->name }} &middot; Superadmin / Owner</span>
                </div>
            </div>

            <div class="w-[62px] h-[62px] rounded-2xl border border-white/35 shadow-md shadow-black/40 overflow-hidden shrink-0 bg-[#0A1E4A] flex items-center justify-center">
                <img src="{{ asset('images/icon-eagle.jpg') }}" alt="Eagle Taekwondo" class="w-full h-full object-cover"
                     onerror="this.style.display='none'; document.getElementById('superDashboardFallback').style.display='flex';">
                <div id="superDashboardFallback" style="display: none;" class="w-full h-full text-white items-center justify-center">
                    <span class="material-symbols-rounded text-3xl">shield_person</span>
                </div>
            </div>
        </div>

        <div class="relative z-10 mt-3 flex flex-wrap items-center gap-x-2.5 gap-y-1 border-t border-white/15 pt-2.5 text-[10.5px] text-white/80">
            <span class="inline-flex items-center gap-1.5">
                <span class="material-symbols-rounded text-sm text-[#7DD3FC]">calendar_today</span>
                <span>{{ $now->translatedFormat('l, d F Y') }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <span class="material-symbols-rounded text-sm text-[#7DD3FC]">schedule</span>
                <span>{{ $now->format('H:i') }} WIB</span>
            </span>
        </div>
    </section>

    <!-- Main Operational Summary (3 Cards Grid - Matching Admin Style) -->
    <section class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Ringkasan Operasional</h2>
            <span class="text-[10px] font-medium text-[#64748B]">{{ $now->translatedFormat('F Y') }}</span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[78px]">
                <div class="flex items-center justify-between">
                    <span class="text-[19px] font-extrabold text-[#7C3AED] leading-none">{{ $activeAssistantCount }}</span>
                    <span class="material-symbols-rounded text-base text-[#7C3AED]">groups</span>
                </div>
                <div class="mt-1.5">
                    <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Asisten Aktif</div>
                    <div class="text-[9.5px] text-[#64748B] truncate">Pelatih terdaftar</div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[78px]">
                <div class="flex items-center justify-between">
                    <span class="text-[19px] font-extrabold text-[#0051D5] leading-none">{{ $todayAttendances->count() }}</span>
                    <span class="material-symbols-rounded text-base text-[#0051D5]">fact_check</span>
                </div>
                <div class="mt-1.5">
                    <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Presensi Hari Ini</div>
                    <div class="text-[9.5px] text-[#64748B] truncate">Semua status</div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[78px]">
                <div class="flex items-center justify-between">
                    <span class="text-[19px] font-extrabold text-[#D97706] leading-none">{{ $upcomingSchedules->count() }}</span>
                    <span class="material-symbols-rounded text-base text-[#D97706]">event_upcoming</span>
                </div>
                <div class="mt-1.5">
                    <div class="text-[11px] font-semibold text-[#131B2E] leading-tight truncate">Jadwal Mendatang</div>
                    <div class="text-[9.5px] text-[#64748B] truncate">Sesi latihan</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Superadmin Quick Actions (With Kelola Pengguna & Log Aktivitas) -->
    <section class="space-y-2">
        <div class="px-0.5">
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Menu Superadmin</h2>
            <p class="text-[10.5px] text-[#64748B] mt-0.5">Akses penuh kontrol sistem, log aktivitas, dan kelola pengguna.</p>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <!-- 1. Kelola Pengguna (Tambah Asisten & Ubah Role) -->
            <a href="{{ route('users.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border-2 border-[#0051D5]/40 shadow-xs hover:border-[#0051D5] transition-all bg-gradient-to-r from-blue-50/40 to-white">
                <span class="w-8 h-8 rounded-lg bg-[#0051D5] text-white flex items-center justify-center shrink-0 shadow-xs">
                    <span class="material-symbols-rounded text-lg">manage_accounts</span>
                </span>
                <div class="min-w-0">
                    <span class="block text-[11px] font-extrabold text-[#00288E] leading-tight">Kelola Pengguna</span>
                    <span class="block text-[9px] text-[#64748B] mt-0.5 leading-tight">Tambah asisten &amp; ubah role</span>
                </div>
            </a>

            <!-- 2. Log Aktivitas Sistem -->
            <a href="{{ route('activity-logs.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border-2 border-amber-500/40 shadow-xs hover:border-amber-500 transition-all bg-gradient-to-r from-amber-50/40 to-white">
                <span class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                    <span class="material-symbols-rounded text-lg">history</span>
                </span>
                <div class="min-w-0">
                    <span class="block text-[11px] font-extrabold text-[#B45309] leading-tight">Log Aktivitas</span>
                    <span class="block text-[9px] text-[#64748B] mt-0.5 leading-tight">Audit trail aktivitas sistem</span>
                </div>
            </a>

            <!-- 3. Jadwal & Materi Latihan -->
            <a href="{{ route('schedules.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-[#D97706]/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">event_available</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Jadwal, Tugas &amp; Materi Latihan</span>
            </a>

            <!-- 4. Rekap Gaji & Atur Tarif -->
            <a href="{{ route('salary.admin.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-emerald-400 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">payments</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Rekap Gaji &amp; Atur Tarif Honor</span>
            </a>

            <!-- 5. Laporan & Export -->
            <a href="{{ route('reports.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-purple-400 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">bar_chart</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Laporan &amp; Export Presensi</span>
            </a>

            <!-- 6. Cabang Dojang -->
            <a href="{{ route('dojangs.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-sky-400 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">domain</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Cabang Unit Dojang</span>
            </a>
        </div>
    </section>

    <!-- Dashboard Content Tabs -->
    <section class="space-y-3">
        <div class="p-1 bg-[#EAEDFF]/70 rounded-xl flex items-center gap-1 border border-[#DAE2FD]/60">
            <button type="button" @click="activePanel = 'attendance'; searchTerm = ''"
                    :class="activePanel === 'attendance' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1 rounded-lg text-[10px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Presensi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'attendance' ? 'bg-[#0051D5] text-white' : 'bg-white text-[#64748B]'">
                    {{ $todayAttendances->count() }}
                </span>
            </button>
            <button type="button" @click="activePanel = 'coaches'; searchTerm = ''"
                    :class="activePanel === 'coaches' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1 rounded-lg text-[10px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Asisten</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'coaches' ? 'bg-[#7C3AED] text-white' : 'bg-white text-[#64748B]'">
                    {{ $coaches->count() }}
                </span>
            </button>
            <button type="button" @click="activePanel = 'schedules'; searchTerm = ''"
                    :class="activePanel === 'schedules' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1 rounded-lg text-[10px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Jadwal</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'schedules' ? 'bg-[#D97706] text-white' : 'bg-white text-[#64748B]'">
                    {{ $upcomingSchedules->count() }}
                </span>
            </button>
            <button type="button" @click="activePanel = 'logs'; searchTerm = ''"
                    :class="activePanel === 'logs' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1 rounded-lg text-[10px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Log</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'logs' ? 'bg-amber-600 text-white' : 'bg-white text-[#64748B]'">
                    {{ $recentLogs->count() }}
                </span>
            </button>
        </div>

        <!-- 1. Tab Presensi -->
        <div x-show="activePanel === 'attendance'" x-cloak class="space-y-3">
            <div class="h-10 bg-white rounded-xl border border-[#DAE2FD] px-3 flex items-center gap-2 shadow-2xs focus-within:border-[#0051D5]">
                <span class="material-symbols-rounded text-lg text-[#0051D5] shrink-0">search</span>
                <input type="text" x-model="searchTerm" placeholder="Cari nama, latihan, atau status presensi..."
                       class="w-full text-xs text-[#131B2E] placeholder-[#64748B] outline-none bg-transparent font-sans">
                <button type="button" x-show="searchTerm" @click="searchTerm = ''" class="text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-rounded text-base">close</span>
                </button>
            </div>

            <div class="space-y-2.5">
                @forelse($todayAttendances as $attendance)
                    @php
                        $activityName = $attendance->trainingSchedule?->activity_name ?? 'Latihan Mandiri';
                        $statusClasses = match ($attendance->status) {
                            'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Izin' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Sakit' => 'bg-blue-50 text-blue-700 border-blue-200',
                            default => 'bg-rose-50 text-rose-700 border-rose-200',
                        };
                    @endphp
                    <article x-show="matchesAttendance(@js(strtolower($attendance->user?->name ?? '')), @js(strtolower($activityName)), @js(strtolower($attendance->status)))"
                             class="bg-white rounded-2xl border border-[#DAE2FD] p-3 shadow-xs transition-all hover:border-[#0051D5]/40">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD]/60 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($attendance->photos->isNotEmpty())
                                        <img src="{{ asset($attendance->photos->first()->photo_path) }}" alt="Foto presensi {{ $attendance->user?->name ?? 'pengguna' }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="material-symbols-rounded text-xl text-[#64748B]">person</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-[13px] font-bold text-[#131B2E] truncate">{{ $attendance->user?->name ?? 'Pengguna' }}</h3>
                                    <p class="text-[10.5px] text-[#64748B] truncate mt-0.5">
                                        {{ substr((string) $attendance->time, 0, 5) }} WIB &bull; {{ $activityName }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusClasses }}">
                                    {{ $attendance->status }}
                                </span>
                                <a href="{{ route('attendances.show', $attendance) }}"
                                   class="block text-[10px] font-bold text-[#0051D5] hover:underline mt-1">
                                    Lihat Bukti &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                        <span class="material-symbols-rounded text-3xl text-slate-300">fact_check</span>
                        <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Presensi Hari Ini</p>
                        <p class="text-[10.5px] text-[#64748B] mt-0.5">Presensi asisten pelatih yang masuk akan tampil otomatis di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 2. Tab Asisten Pelatih -->
        <div x-show="activePanel === 'coaches'" x-cloak class="space-y-2.5">
            @forelse($coaches as $coach)
                <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3 shadow-xs flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-xl">sports_martial_arts</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-[#131B2E] truncate">{{ $coach->name }}</h4>
                            <p class="text-[10.5px] text-[#64748B] truncate mt-0.5">
                                {{ $coach->belt_level ?: 'Pelatih' }} &bull; {{ $coach->dojang?->name ?? 'Dojang Pusat' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $coach->monthly_hadir_count }} Kehadiran
                        </span>
                        <p class="text-[10px] font-mono text-[#0051D5] font-semibold mt-0.5">
                            Rp {{ number_format($coach->rate_per_session, 0, ',', '.') }}/sesi
                        </p>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                    <span class="material-symbols-rounded text-3xl text-slate-300">groups</span>
                    <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Asisten Terdaftar</p>
                </div>
            @endforelse
        </div>

        <!-- 3. Tab Jadwal -->
        <div x-show="activePanel === 'schedules'" x-cloak class="space-y-2.5">
            @forelse($upcomingSchedules as $sched)
                <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3 shadow-xs space-y-1.5">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-bold text-[#131B2E] truncate">{{ $sched->activity_name }}</h4>
                        <span class="text-[10px] font-semibold text-[#0051D5] px-2 py-0.5 rounded-full bg-[#EAEDFF]">
                            {{ $sched->date->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <p class="text-[10.5px] text-[#64748B] flex items-center gap-1">
                        <span class="material-symbols-rounded text-xs text-[#0051D5]">schedule</span>
                        {{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }} WIB
                    </p>
                    <p class="text-[10.5px] text-[#475569] flex items-center gap-1 truncate">
                        <span class="material-symbols-rounded text-xs text-[#0051D5]">location_on</span>
                        {{ $sched->location }}
                    </p>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                    <span class="material-symbols-rounded text-3xl text-slate-300">event_upcoming</span>
                    <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Jadwal Mendatang</p>
                </div>
            @endforelse
        </div>

        <!-- 4. Tab Log Aktivitas Sistem -->
        <div x-show="activePanel === 'logs'" x-cloak class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-[#131B2E]">Aktivitas Sistem Terkini</span>
                <a href="{{ route('activity-logs.index') }}" class="text-[11px] font-bold text-[#0051D5] hover:underline flex items-center gap-0.5">
                    <span>Semua Log</span>
                    <span class="material-symbols-rounded text-xs">arrow_forward</span>
                </a>
            </div>

            @forelse($recentLogs as $log)
                <div class="bg-white rounded-xl border border-[#DAE2FD] p-3 shadow-2xs space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold
                                {{ $log->action === 'LOGIN' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                                  ($log->action === 'CHECK_IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                                   ($log->action === 'DELETE_SCHEDULE' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                    'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                                {{ $log->action }}
                            </span>
                            <span class="text-xs font-bold text-[#131B2E] truncate">{{ $log->user?->name ?? 'Sistem' }}</span>
                        </div>
                        <span class="text-[9.5px] text-[#64748B] shrink-0 font-mono">
                            {{ $log->created_at->format('H:i') }} WIB
                        </span>
                    </div>
                    <p class="text-[11px] text-[#475569] leading-relaxed">{{ $log->description }}</p>
                    <p class="text-[9.5px] text-[#94A3B8] font-mono">{{ $log->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                    <span class="material-symbols-rounded text-3xl text-slate-300">history</span>
                    <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Log Aktivitas</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
