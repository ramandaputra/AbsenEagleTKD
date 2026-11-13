@extends('layouts.app')

@section('title', 'Dashboard Admin / Pelatih')

@section('content')
@php
    $attendanceCounts = [
        'hadir' => $todayAttendances->where('status', 'Hadir')->count(),
        'izin' => $todayAttendances->where('status', 'Izin')->count(),
        'sakit_alpa' => $todayAttendances->whereIn('status', ['Sakit', 'Alpa'])->count(),
    ];
    $todayMaterialSchedule = $todaySchedules->firstWhere('activity_name', 'Latihan Reguler');
@endphp

<div
    x-data="{
        activePanel: 'attendance',
        searchTerm: '',
        showMaterialsModal: @js($errors->has('description') || $errors->has('date')),
        showRateModal: @js($errors->has('rate_per_session')),
        matchesAttendance(name, activity, status) {
            const query = this.searchTerm.trim().toLowerCase();

            return !query || (name + ' ' + activity + ' ' + status).includes(query);
        }
    }"
    class="space-y-3.5"
>
    <!-- Executive Header Banner -->
    <section class="rounded-2xl p-4 text-white shadow-lg relative overflow-hidden bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] border border-white/15">
        <div class="absolute -right-5 -top-5 w-36 h-36 rounded-full bg-blue-500/20 blur-xl pointer-events-none"></div>
        <div class="absolute -left-6 -bottom-6 w-32 h-32 rounded-full bg-cyan-400/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500/25 text-blue-100 border border-blue-300/40">
                    Dashboard Admin / Pelatih
                </span>
                <h1 class="text-sm font-extrabold text-white tracking-tight mt-1.5 leading-snug">
                    Eagle Taekwondo Academy
                </h1>
                <p class="text-[11.5px] font-semibold text-[#7DD3FC] mt-0.5">
                    Presensi &amp; aktivitas latihan
                </p>
                <div class="flex items-center space-x-1.5 mt-2 text-[10px] text-white/80 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="truncate">Halo, {{ $user->name }} &middot; {{ $user->role_display_name }}</span>
                </div>
            </div>

            <div class="w-[62px] h-[62px] rounded-2xl border border-white/35 shadow-md shadow-black/40 overflow-hidden shrink-0 bg-[#0A1E4A] flex items-center justify-center">
                <img src="{{ asset('images/favicon.png') }}" alt="Eagle Taekwondo" class="w-full h-full object-cover"
                     onerror="this.style.display='none'; document.getElementById('adminDashboardFallback').style.display='flex';">
                <div id="adminDashboardFallback" style="display: none;" class="w-full h-full text-white items-center justify-center">
                    <span class="material-symbols-rounded text-3xl">sports_martial_arts</span>
                </div>
            </div>
        </div>

        <div class="relative z-10 mt-3 pt-2.5 border-t border-white/15 flex items-center gap-1.5 text-[10.5px] text-white/80">
            <span class="material-symbols-rounded text-sm text-[#7DD3FC]">calendar_today</span>
            <span>{{ $now->translatedFormat('l, d F Y') }}</span>
            <span class="mx-0.5 text-white/40">&bull;</span>
            <span>{{ $now->format('H:i') }} WIB</span>
        </div>
    </section>

    <!-- Main Operational Summary -->
    <section class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Ringkasan Operasional</h2>
            <span class="text-[10px] font-medium text-[#64748B]">{{ $now->translatedFormat('F Y') }}</span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs flex flex-col justify-between min-h-[78px]">
                <div class="flex items-center justify-between">
                    <span class="text-[19px] font-extrabold text-[#7C3AED] leading-none">{{ $coaches->count() }}</span>
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

    <!-- Admin/Pelatih Quick Actions -->
    <section class="space-y-2">
        <div class="px-0.5">
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Menu Admin / Pelatih</h2>
            <p class="text-[10.5px] text-[#64748B] mt-0.5">Akses cepat untuk kegiatan hari ini dan pengelolaan asisten.</p>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="showMaterialsModal = true"
                    class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-[#0051D5]/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">menu_book</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Materi Latihan Hari Ini</span>
            </button>

            <a href="{{ route('schedules.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-[#D97706]/40 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">event_available</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Jadwal &amp; Tugas Asisten</span>
            </a>

            <a href="{{ route('salary.admin.index') }}"
               class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-emerald-400 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">payments</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Rekap Hadir &amp; Gaji Bulanan</span>
            </a>

            <button type="button" @click="showRateModal = true"
                    class="min-h-[72px] flex items-center gap-2.5 text-left bg-white rounded-xl p-2.5 border border-[#DAE2FD]/70 shadow-2xs hover:border-violet-400 transition-colors">
                <span class="w-8 h-8 rounded-lg bg-violet-50 text-violet-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-lg">price_change</span>
                </span>
                <span class="block min-w-0 text-[10.5px] font-bold text-[#131B2E] leading-tight">Atur Gaji per Pertemuan</span>
            </button>
        </div>
    </section>

    <!-- Live Attendance Summary -->
    <section class="bg-white rounded-xl px-3.5 py-2.5 border border-[#DAE2FD]/70 shadow-2xs flex items-center justify-between">
        <div class="flex items-center space-x-2 flex-1 min-w-0">
            <span class="w-2 h-2 rounded-full bg-[#0051D5] shrink-0"></span>
            <div class="min-w-0">
                <div class="text-xs font-extrabold text-[#0051D5] leading-none">{{ $attendanceCounts['hadir'] }}</div>
                <div class="text-[9.5px] text-[#64748B] truncate mt-0.5">Hadir</div>
            </div>
        </div>

        <div class="w-px h-5 bg-[#DAE2FD] mx-2"></div>

        <div class="flex items-center space-x-2 flex-1 min-w-0">
            <span class="w-2 h-2 rounded-full bg-[#D97706] shrink-0"></span>
            <div class="min-w-0">
                <div class="text-xs font-extrabold text-[#D97706] leading-none">{{ $attendanceCounts['izin'] }}</div>
                <div class="text-[9.5px] text-[#64748B] truncate mt-0.5">Izin</div>
            </div>
        </div>

        <div class="w-px h-5 bg-[#DAE2FD] mx-2"></div>

        <div class="flex items-center space-x-2 flex-1 min-w-0">
            <span class="w-2 h-2 rounded-full bg-[#DC2626] shrink-0"></span>
            <div class="min-w-0">
                <div class="text-xs font-extrabold text-[#DC2626] leading-none">{{ $attendanceCounts['sakit_alpa'] }}</div>
                <div class="text-[9.5px] text-[#64748B] truncate mt-0.5">Sakit / Alpa</div>
            </div>
        </div>
    </section>

    <!-- Dashboard Content Tabs -->
    <section class="space-y-3">
        <div class="p-1 bg-[#EAEDFF]/70 rounded-xl flex items-center gap-1 border border-[#DAE2FD]/60">
            <button type="button" @click="activePanel = 'attendance'; searchTerm = ''"
                    :class="activePanel === 'attendance' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1.5 rounded-lg text-[10.5px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Presensi</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'attendance' ? 'bg-[#0051D5] text-white' : 'bg-white text-[#64748B]'">
                    {{ $todayAttendances->count() }}
                </span>
            </button>
            <button type="button" @click="activePanel = 'coaches'; searchTerm = ''"
                    :class="activePanel === 'coaches' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1.5 rounded-lg text-[10.5px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Asisten</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'coaches' ? 'bg-[#7C3AED] text-white' : 'bg-white text-[#64748B]'">
                    {{ $coaches->count() }}
                </span>
            </button>
            <button type="button" @click="activePanel = 'schedules'; searchTerm = ''"
                    :class="activePanel === 'schedules' ? 'bg-white text-[#131B2E] font-bold shadow-xs' : 'text-[#64748B] hover:text-[#131B2E] font-medium'"
                    class="flex-1 py-2 px-1.5 rounded-lg text-[10.5px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                <span>Jadwal</span>
                <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold"
                      :class="activePanel === 'schedules' ? 'bg-[#D97706] text-white' : 'bg-white text-[#64748B]'">
                    {{ $upcomingSchedules->count() }}
                </span>
            </button>
        </div>

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
                        $activityName = $attendance->trainingSchedule?->activity_name ?? 'Latihan';
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
                            <span class="shrink-0 px-2 py-1 rounded-full border text-[9.5px] font-bold {{ $statusClasses }}">
                                {{ $attendance->status }}
                            </span>
                        </div>
                        @if($attendance->notes)
                            <p class="mt-2 pt-2 border-t border-[#F1F5F9] text-[11px] text-[#64748B] leading-snug">{{ $attendance->notes }}</p>
                        @endif
                    </article>
                @empty
                    <div class="py-8 text-center bg-white rounded-2xl border border-[#DAE2FD] px-5 shadow-xs">
                        <span class="material-symbols-rounded text-4xl text-slate-300">event_busy</span>
                        <h3 class="text-xs font-bold text-slate-800 mt-2">Belum Ada Presensi Hari Ini</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Presensi asisten pelatih akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div x-show="activePanel === 'coaches'" x-cloak class="space-y-2.5">
            <div class="flex items-center justify-between px-0.5">
                <div>
                    <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Rekap Asisten Pelatih</h2>
                    <p class="text-[10.5px] text-[#64748B] mt-0.5">Periode {{ $now->translatedFormat('F Y') }}</p>
                </div>
            </div>

            @forelse($coaches as $coach)
                @php $summary = $coach->monthly_summary; @endphp
                <article class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <h3 class="text-xs font-bold text-[#131B2E] truncate">{{ $coach->name }}</h3>
                                <span class="shrink-0 text-[9px] px-1.5 py-0.5 rounded-full bg-[#EAEDFF] text-[#0051D5] font-semibold">
                                    {{ $coach->belt_level ?: 'Asisten Pelatih' }}
                                </span>
                            </div>
                            <p class="text-[10.5px] text-[#64748B] mt-1">
                                Hadir <strong class="text-emerald-700">{{ $summary['total_approved_sessions'] }}</strong>
                                &nbsp;&bull;&nbsp; Izin <strong class="text-amber-700">{{ $summary['total_izin_sessions'] }}</strong>
                                &nbsp;&bull;&nbsp; Sakit <strong class="text-blue-700">{{ $summary['total_sakit_sessions'] }}</strong>
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[13px] font-extrabold text-emerald-700">
                                Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
                            </div>
                            <div class="text-[9.5px] text-[#64748B]">Estimasi honor</div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="py-8 text-center bg-white rounded-2xl border border-[#DAE2FD] px-5 shadow-xs">
                    <span class="material-symbols-rounded text-4xl text-slate-300">groups</span>
                    <h3 class="text-xs font-bold text-slate-800 mt-2">Belum Ada Asisten Pelatih</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Asisten pelatih aktif akan tercatat di sini.</p>
                </div>
            @endforelse
        </div>

        <div x-show="activePanel === 'schedules'" x-cloak class="space-y-2.5">
            <div class="px-0.5">
                <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Jadwal Latihan Mendatang</h2>
                <p class="text-[10.5px] text-[#64748B] mt-0.5">Sesi latihan terdekat dan jumlah asisten bertugas.</p>
            </div>

            @forelse($upcomingSchedules as $schedule)
                @php
                    $assignmentCount = $schedule->dutySchedule?->assignments?->count() ?? 0;
                @endphp
                <article class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex flex-col items-center justify-center shrink-0">
                        <span class="material-symbols-rounded text-xl">calendar_month</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xs font-bold text-[#131B2E] truncate">{{ $schedule->activity_name }}</h3>
                        <p class="text-[10.5px] text-[#64748B] mt-0.5 truncate">
                            {{ $schedule->date->translatedFormat('D, d M') }} &bull; {{ substr((string) $schedule->start_time, 0, 5) }} WIB
                        </p>
                        <p class="text-[10px] text-[#0051D5] mt-1 font-semibold truncate">
                            {{ $assignmentCount }} asisten ditugaskan
                        </p>
                    </div>
                    <span class="material-symbols-rounded text-xl text-slate-400 shrink-0">chevron_right</span>
                </article>
            @empty
                <div class="py-8 text-center bg-white rounded-2xl border border-[#DAE2FD] px-5 shadow-xs">
                    <span class="material-symbols-rounded text-4xl text-slate-300">event_available</span>
                    <h3 class="text-xs font-bold text-slate-800 mt-2">Belum Ada Jadwal</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Jadwal latihan berikutnya akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Modal: Today's Training Description -->
    <div x-show="showMaterialsModal" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl bg-white border border-[#DAE2FD] p-4 shadow-2xl space-y-3"
             @click.away="showMaterialsModal = false">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[#131B2E]">Deskripsi Materi Latihan</h2>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Tulis materi untuk latihan hari ini.</p>
                </div>
                <button type="button" @click="showMaterialsModal = false" class="w-8 h-8 rounded-lg text-[#64748B] hover:bg-[#F2F3FF] flex items-center justify-center">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form action="{{ route('schedules.materials.today') }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="date" value="{{ $now->toDateString() }}">

                <div>
                    <label for="training-date-today" class="block text-[10.5px] font-semibold text-[#131B2E] mb-1.5">Tanggal Latihan</label>
                    <input id="training-date-today" type="date" value="{{ $now->toDateString() }}" disabled
                           class="w-full h-10 rounded-xl border border-[#DAE2FD] bg-[#F8FAFC] px-3 text-xs text-[#64748B]">
                    @error('date')
                        <p class="text-[10.5px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="training-description" class="block text-[10.5px] font-semibold text-[#131B2E] mb-1.5">Deskripsi Materi</label>
                    <textarea id="training-description" name="description" rows="4" required maxlength="1000"
                              placeholder="Contoh: pemanasan, poomsae, dan teknik tendangan"
                              class="w-full rounded-xl border border-[#DAE2FD] bg-white p-2.5 text-xs text-[#131B2E] placeholder:text-[#94A3B8] focus:outline-none focus:border-[#0051D5] focus:ring-2 focus:ring-[#0051D5]/10">{{ old('description', $todayMaterialSchedule?->description ?? '') }}</textarea>
                    @error('description')
                        <p class="text-[10.5px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full h-10 rounded-xl bg-[#0051D5] text-white text-xs font-bold hover:bg-[#003ea8] transition-colors">
                    Simpan Deskripsi Materi
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Update Assistant Session Rates -->
    <div x-show="showRateModal" x-cloak
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl bg-white border border-[#DAE2FD] p-4 shadow-2xl space-y-3"
             @click.away="showRateModal = false">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[#131B2E]">Gaji Asisten per Pertemuan</h2>
                    <p class="text-[11px] text-[#64748B] mt-0.5">Atur tarif honor untuk setiap asisten pelatih.</p>
                </div>
                <button type="button" @click="showRateModal = false" class="w-8 h-8 rounded-lg text-[#64748B] hover:bg-[#F2F3FF] flex items-center justify-center">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            @error('rate_per_session')
                <p class="text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
            @enderror

            @forelse($coaches as $coach)
                <form action="{{ route('salary.admin.coach-rate', $coach) }}" method="POST"
                      class="rounded-xl border border-[#DAE2FD] p-3 flex items-center gap-2.5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="coach_id" value="{{ $coach->id }}">
                    <div class="min-w-0 flex-1">
                        <label for="rate-{{ $coach->id }}" class="block text-[11px] font-bold text-[#131B2E] truncate">{{ $coach->name }}</label>
                        <div class="relative mt-1.5">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] font-semibold text-[#64748B]">Rp</span>
                            <input id="rate-{{ $coach->id }}" type="number" name="rate_per_session" min="0" step="5000" required
                                   value="{{ old('coach_id') == $coach->id ? old('rate_per_session') : $coach->rate_per_session }}"
                                   class="w-full h-9 rounded-lg border border-[#DAE2FD] pl-8 pr-2 text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        </div>
                    </div>
                    <button type="submit" class="mt-5 h-9 px-3 rounded-lg bg-[#0051D5] text-white text-[10.5px] font-bold hover:bg-[#003ea8] transition-colors">
                        Simpan
                    </button>
                </form>
            @empty
                <div class="py-6 text-center rounded-xl border border-dashed border-[#DAE2FD] bg-[#F8FAFC]">
                    <span class="material-symbols-rounded text-3xl text-[#64748B]">groups</span>
                    <p class="text-xs font-bold text-[#131B2E] mt-1">Belum ada asisten pelatih aktif</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
