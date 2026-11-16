@extends('layouts.app')

@section('title', 'Beranda Asisten Pelatih')

@section('content')
<div x-data="asistenHomeApp()" x-init="initClock()" class="space-y-4">

    {{-- 1. HERO DIGITAL CLOCK CARD (Matching Tirtafield CorporateHeroClock exactly) --}}
    <div class="relative overflow-hidden bg-white rounded-2xl border border-[#DAE2FD] p-5 shadow-xs text-center">
        <!-- Ambient Subtle Decorative Blob -->
        <div class="absolute -right-5 -top-5 w-32 h-32 rounded-full bg-[#316BF3]/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center justify-center">
            <!-- Greeting Centered -->
            <div class="text-sm font-semibold text-[#0051D5] tracking-tight mb-1.5">
                <span x-text="greeting">Selamat Datang</span>, {{ $user->name }}
            </div>

            <!-- Jam Digital Centrify HH:mm:ss WIB -->
            <div class="flex items-baseline justify-center space-x-1.5 my-1">
                <span class="font-sans text-[34px] font-bold tracking-tight text-[#131B2E]" x-text="clockTime">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }}
                </span>
                <span class="text-xs font-bold text-[#00288E]">WIB</span>
            </div>

            <!-- Tanggal di bawah jam -->
            <div class="text-[13.5px] text-[#64748B] font-normal mt-0.5">
                {{ $now->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </div>

            <!-- Penempatan Dojang & Sabuk (Diambil langsung dari tabel database users & dojangs) -->
            @if($user->dojang || $user->belt_level)
                <div class="inline-flex items-center justify-center mt-3 max-w-full">
                    <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-[#0051D5]/10 border border-[#0051D5]/20 text-[#0051D5] text-[11.5px] font-semibold max-w-full truncate">
                        <span class="material-symbols-rounded text-sm">sports_martial_arts</span>
                        @if($user->dojang)
                            <span class="truncate">{{ $user->dojang->name }}</span>
                        @endif
                        @if($user->belt_level)
                            <span class="px-1.5 py-0.2 bg-[#0051D5] text-white text-[10px] font-bold rounded-md ml-1 shrink-0">{{ $user->belt_level }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 2. PRESENSI LATIHAN HARI INI (Matching Tirtafield CorporateAttendanceCards) --}}
    <div class="space-y-2.5">
        @if($todayAttendance)
            <!-- Sesi Selesai (Completed Attendance Card) -->
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 flex items-center justify-between shadow-xs hover:border-[#059669] transition-all">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-[#059669] flex items-center justify-center text-lg shrink-0">
                        <span class="material-symbols-rounded text-2xl">check_circle</span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-[14.5px] font-bold text-[#131B2E] truncate">Presensi Sesi Tercatat</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-emerald-100 text-emerald-800 shrink-0">
                                {{ strtoupper($todayAttendance->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-[#64748B] mt-0.5 truncate">
                            {{ $todayAttendance->trainingSchedule ? $todayAttendance->trainingSchedule->activity_name : 'Latihan Reguler' }} · {{ substr($todayAttendance->time, 0, 5) }} WIB
                        </p>
                    </div>
                </div>

                @if($todayAttendance->photos->isNotEmpty())
                    <div class="w-11 h-11 rounded-xl overflow-hidden border border-[#DAE2FD] shadow-xs shrink-0 ml-2">
                        <img src="/{{ $todayAttendance->photos->first()->photo_path }}" alt="Bukti Foto" class="w-full h-full object-cover">
                    </div>
                @else
                    <span class="material-symbols-rounded text-[22px] text-emerald-600 shrink-0 ml-2">verified</span>
                @endif
            </div>
        @else
            <!-- Sesi Aktif (Active Attendance Card matching Tirtafield) -->
            <div class="bg-white rounded-2xl border-2 border-[#316BF3] shadow-[0_8px_24px_rgba(49,107,243,0.18)] p-4 relative overflow-hidden">
                <!-- Top-Right Ribbon: SESI AKTIF -->
                <div class="absolute top-0 right-0 px-3 py-1 bg-[#316BF3] rounded-bl-xl text-white text-[10px] font-bold tracking-wider">
                    SESI AKTIF
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-11 h-11 rounded-xl bg-[#DAE2FD] text-[#00288E] flex items-center justify-center text-lg shrink-0 mt-0.5">
                        <span class="material-symbols-rounded text-[24px]">photo_camera</span>
                    </div>
                    <div class="flex-1 pr-14">
                        <div class="text-[11px] font-bold text-[#0051D5] tracking-wide uppercase">ABSENSI MANDIRI</div>
                        <h3 class="text-base font-bold text-[#131B2E] mt-0.5">Presensi Kehadiran Pelatih</h3>
                        <p class="text-xs text-[#64748B] mt-0.5">
                            Ambil foto bukti kehadiran untuk verifikasi dan pencatatan honor sesi latihan hari ini.
                        </p>
                    </div>
                </div>

                <hr class="border-t border-[#EAEDFF] my-3">

                <a href="{{ route('attendances.create') }}"
                   class="w-full h-12 rounded-xl bg-[#316BF3] hover:bg-[#2558d4] text-white font-bold text-[14.5px] flex items-center justify-center space-x-2 shadow-sm transition-all active:scale-[0.99]">
                    <span class="material-symbols-rounded text-xl">camera_alt</span>
                    <span>Check-in Absensi Mandiri Sekarang</span>
                </a>
            </div>
        @endif
    </div>

    {{-- 3. DESKRIPSI MATERI LATIHAN HARI INI (Tepat Di Bawah Tombol Presensi) --}}
    <section class="bg-white rounded-2xl sm:rounded-3xl border border-[#DAE2FD] p-4 sm:p-5 shadow-xs space-y-3.5 w-full min-w-0 overflow-hidden">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#131B2E] flex items-center gap-1.5 min-w-0">
                <span class="material-symbols-rounded text-[#00288E] text-lg shrink-0">menu_book</span>
                <span class="truncate">Materi Latihan Hari Ini</span>
            </h2>
            @if($todaySchedule)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#EAEDFF] text-[#00288E] shrink-0">
                    {{ $todaySchedule->date->isToday() ? 'Sesi Hari Ini' : 'Sesi Terdekat' }}
                </span>
            @endif
        </div>

        @if($todaySchedule)
            <!-- Sesi & Lokasi Card -->
            <div class="p-3.5 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] space-y-2 w-full min-w-0 overflow-hidden">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-[#131B2E] break-words">
                        {{ $todaySchedule->activity_name }}
                    </h3>
                </div>
                <div class="space-y-1.5 text-xs text-[#64748B] pt-0.5">
                    <div class="flex items-center gap-2 font-medium">
                        <span class="material-symbols-rounded text-sm text-[#0051D5] shrink-0">schedule</span>
                        <span>{{ substr($todaySchedule->start_time, 0, 5) }} - {{ substr($todaySchedule->end_time, 0, 5) }} WIB</span>
                    </div>
                    <div class="flex items-start gap-2 font-medium min-w-0">
                        <span class="material-symbols-rounded text-sm text-[#0051D5] shrink-0 mt-0.5">location_on</span>
                        <span class="break-words leading-relaxed text-[#475569] flex-1 min-w-0">{{ $todaySchedule->location }}</span>
                    </div>
                </div>
            </div>

            <!-- Deskripsi / Kurikulum Latihan -->
            <div class="w-full min-w-0">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-[#64748B] mb-1.5">
                    Rincian &amp; Fokus Materi Latihan
                </label>
                <div class="p-3.5 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-xs text-[#131B2E] leading-relaxed break-words w-full min-w-0 overflow-hidden">
                    @if($todaySchedule->description)
                        <p class="whitespace-pre-line break-words">{{ $todaySchedule->description }}</p>
                    @else
                        <p class="text-[#64748B] italic">Materi latihan reguler: Poomsae dasar, drill tendangan (Kyorugi), fisik dan peregangan taekwondo.</p>
                    @endif
                </div>
            </div>

            <!-- Tugas Khusus Anda (Jika Ada Penugasan Piket) -->
            @if($todayDuty)
                <div class="p-3.5 rounded-xl bg-[#0051D5]/5 border border-[#0051D5]/20 space-y-1.5 w-full min-w-0 overflow-hidden">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#0051D5] flex items-center gap-1 min-w-0">
                            <span class="material-symbols-rounded text-sm shrink-0">assignment_ind</span>
                            <span class="truncate">Tugas Khusus Mengajar Anda</span>
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#0051D5] text-white shrink-0">
                            {{ $todayDuty->role_task }}
                        </span>
                    </div>
                    @if($todayDuty->training_materials)
                        <p class="text-xs text-[#131B2E] font-medium pt-0.5 break-words">
                            <span class="font-bold text-[#00288E]">Target Materi:</span> {{ $todayDuty->training_materials }}
                        </p>
                    @endif
                </div>
            @endif

            <!-- Instruktur & Asisten Bertugas -->
            @if($todaySchedule->dutySchedule && $todaySchedule->dutySchedule->assignments->isNotEmpty())
                <div class="pt-1 w-full min-w-0">
                    <label class="block text-[10.5px] font-semibold text-[#64748B] mb-2">
                        Pelatih &amp; Asisten Yang Bertugas:
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full min-w-0">
                        @foreach($todaySchedule->dutySchedule->assignments as $asg)
                            <div class="flex items-center gap-2.5 p-2.5 rounded-xl text-xs font-semibold {{ $asg->user_id === $user->id ? 'bg-[#00288E] text-white shadow-xs' : 'bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E]' }} min-w-0 w-full overflow-hidden">
                                <div class="w-8 h-8 rounded-lg {{ $asg->user_id === $user->id ? 'bg-white/20 text-white' : 'bg-[#EAEDFF] text-[#0051D5]' }} flex items-center justify-center shrink-0">
                                    <span class="material-symbols-rounded text-base">{{ $asg->user_id === $user->id ? 'person' : 'sports_martial_arts' }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate leading-tight font-bold">{{ $asg->user?->name ?? 'Pelatih' }}</p>
                                    <p class="text-[10px] {{ $asg->user_id === $user->id ? 'text-white/80' : 'text-[#64748B]' }} truncate leading-tight mt-0.5">
                                        {{ $asg->role_task }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <!-- Empty state jika tidak ada jadwal hari ini -->
            <div class="text-center py-6">
                <div class="w-12 h-12 rounded-full bg-[#F2F3FF] text-[#64748B] flex items-center justify-center mx-auto mb-2">
                    <span class="material-symbols-rounded text-2xl">event_available</span>
                </div>
                <h4 class="text-xs font-bold text-[#131B2E]">Tidak Ada Jadwal Latihan Khusus Hari Ini</h4>
                <p class="text-[11px] text-[#64748B] mt-1 max-w-[280px] mx-auto">
                    Silakan ikuti instruksi dari Pelatih Utama untuk materi latihan mandiri atau sesi berikutnya.
                </p>
            </div>
        @endif
    </div>

    <!-- Discrete Brand Footer -->
    <div class="text-center text-[10.5px] text-[#64748B] pt-1 pb-2">
        Eagle Taekwondo Academy • Sistem Presensi Pelatih v2.0
    </div>

</div>

<script>
    function asistenHomeApp() {
        return {
            clockTime: '{{ \Carbon\Carbon::now("Asia/Jakarta")->format("H:i:s") }}',
            greeting: 'Selamat Datang',

            initClock() {
                this.updateGreeting();
                setInterval(() => {
                    const now = new Date();
                    const h = String(now.getHours()).padStart(2, '0');
                    const m = String(now.getMinutes()).padStart(2, '0');
                    const s = String(now.getSeconds()).padStart(2, '0');
                    this.clockTime = `${h}:${m}:${s}`;
                }, 1000);
            },

            updateGreeting() {
                const hour = new Date().getHours();
                if (hour >= 4 && hour < 11) {
                    this.greeting = 'Selamat Pagi';
                } else if (hour >= 11 && hour < 15) {
                    this.greeting = 'Selamat Siang';
                } else if (hour >= 15 && hour < 18) {
                    this.greeting = 'Selamat Sore';
                } else {
                    this.greeting = 'Selamat Malam';
                }
            }
        };
    }
</script>
@endsection
