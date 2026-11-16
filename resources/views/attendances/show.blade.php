@extends('layouts.app')

@section('title', 'Bukti Presensi')

@section('content')
@php
    $photo = $attendance->photos->first();
    $photoUrl = $photo ? asset($photo->photo_path) : null;
    $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $attendance->user?->name ?? 'User');
    $dateStr = $attendance->date ? $attendance->date->format('dmY') : now()->format('dmY');
    $ext = $photo ? (pathinfo($photo->photo_path, PATHINFO_EXTENSION) ?: 'jpg') : 'jpg';
    if (strtolower($ext) === 'jpeg') {
        $ext = 'jpg';
    }
    // Format Namatanggal: e.g. Raja16112026.jpg
    $photoFileName = $cleanName . $dateStr . '.' . $ext;
    $timeOnly = substr((string) $attendance->time, 0, 5);
@endphp

<div x-data="{ showModal: false }" class="space-y-4 pb-20">

    <!-- 1. VERIFIED SUCCESS STATE ANIMATION EMBLEM (Matching Tirtafield BuktiPresensiBerhasilScreen) -->
    <section class="flex flex-col items-center pt-2 pb-1 text-center">
        <!-- Pulsing Circle Emblem -->
        <div class="relative w-20 h-20 flex items-center justify-center">
            <!-- Pulsing outer halo -->
            <span class="absolute w-20 h-20 rounded-full bg-[#4EDEA3]/25 animate-ping opacity-75"></span>
            <span class="absolute w-18 h-18 rounded-full bg-[#4EDEA3]/20"></span>
            <!-- Center checkmark circle -->
            <div class="relative w-15 h-15 rounded-full bg-[#00563A] text-white flex items-center justify-center shadow-lg shadow-[#00563A]/30">
                <span class="material-symbols-rounded text-white text-[36px] fill">check_circle</span>
            </div>
        </div>

        <h2 class="mt-3.5 text-xl font-bold text-[#131B2E] tracking-tight font-sans">
            Presensi Berhasil Terkirim!
        </h2>
        <p class="mt-1.5 text-[13px] text-[#64748B] px-4 max-w-[340px] leading-relaxed font-sans">
            Data kehadiran Anda telah tercatat dan terverifikasi di sistem Eagle TKD.
        </p>
    </section>

    <!-- 2. ATTENDANCE DIGITAL SLIP / RECEIPT CARD (Matching Tirtafield _buildReceiptCard) -->
    <section class="w-full bg-white rounded-2xl sm:rounded-3xl border border-[#DAE2FD] shadow-[0_8px_30px_rgba(15,23,42,0.06)] overflow-hidden">
        <!-- Top Decorative Gradient Strip: #0051D5 to #4EDEA3 -->
        <div class="h-1 w-full bg-gradient-to-r from-[#0051D5] to-[#4EDEA3]"></div>

        <div class="p-4 sm:p-5 space-y-4">
            <!-- Receipt Header Pill Badge -->
            <div class="flex justify-center">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#EAEDFF] border border-[#DAE2FD]/80 text-[#131B2E]">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0051D5] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#0051D5]"></span>
                    </span>
                    <span class="text-[11.5px] font-semibold">Tercatat: Presensi Sah &amp; Terverifikasi</span>
                </div>
            </div>

            <!-- Shift & Status Category Box -->
            <div class="p-3 rounded-xl bg-[#F2F3FF] border border-[#EAEDFF] flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-[#DBE1FF] text-[#00288E] flex items-center justify-center shrink-0">
                        <span class="material-symbols-rounded text-xl">sports_martial_arts</span>
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-[13.5px] text-[#131B2E] truncate">
                            {{ $attendance->trainingSchedule?->activity_name ?? 'Presensi Sesi Latihan' }}
                        </h4>
                        <p class="text-[11.5px] text-[#64748B] truncate mt-0.5">
                            {{ $attendance->user->dojang?->name ?? 'Dojang Eagle TKD' }} • Sesi Pelatih
                        </p>
                    </div>
                </div>
                <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold {{ $attendance->status === 'Hadir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $attendance->status ?? 'Hadir' }}
                </span>
            </div>

            @if($attendance->notes)
                <!-- Notes Box if any -->
                <div class="p-2.5 rounded-xl bg-amber-50/80 border border-amber-200 flex items-start gap-2 text-xs text-amber-900">
                    <span class="material-symbols-rounded text-amber-600 text-base shrink-0 mt-0.5">info</span>
                    <div>
                        <p class="font-bold text-[11px] text-amber-800">Catatan Presensi:</p>
                        <p class="text-[11.5px] mt-0.5 italic text-amber-900">{{ $attendance->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Metric 1: Timestamp Item -->
            <div class="flex items-start gap-3 pt-1">
                <div class="w-8.5 h-8.5 rounded-full bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0 mt-0.5">
                    <span class="material-symbols-rounded text-[18px]">schedule</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-semibold text-[#64748B] tracking-wider uppercase">Waktu Stempel</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span class="font-mono text-[17px] font-bold text-[#131B2E]">{{ $timeOnly }}</span>
                        <span class="text-[11.5px] font-semibold text-[#64748B]">WIB</span>
                    </div>
                    <p class="text-[12.5px] text-[#64748B] mt-0.5">{{ $attendance->date->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-[#DAE2FD]/60"></div>

            <!-- Metric 2: Lokasi Penugasan / Latihan Item -->
            <div class="flex items-start gap-3">
                <div class="w-8.5 h-8.5 rounded-full bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0 mt-0.5">
                    <span class="material-symbols-rounded text-[18px]">location_on</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-semibold text-[#64748B] tracking-wider uppercase">Lokasi Penugasan</p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#0051D5]/10 border border-[#0051D5]/30 text-[10.5px] font-bold text-[#0051D5] shrink-0">
                            <span class="material-symbols-rounded text-xs">share_location</span>
                            <span>Penugasan Dojang</span>
                        </span>
                    </div>
                    <h5 class="font-bold text-[14px] text-[#131B2E] mt-1 leading-snug">
                        {{ $attendance->user->dojang?->name ?? 'Dojang Eagle Taekwondo Club' }}
                    </h5>
                    <p class="text-xs text-[#64748B] mt-0.5 leading-relaxed">
                        {{ $attendance->user->dojang?->address ?? 'Pusat Pelatihan Eagle Taekwondo Tanjungpinang' }}
                    </p>
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-[#DAE2FD]/60"></div>

            <!-- Metric 3: Attached Selfie Snapshot (Clean without Berstempel Lokasi or Foto Presensi Sah) -->
            <div class="flex items-center gap-3">
                <!-- Thumbnail -->
                @if($photoUrl)
                    <button type="button" @click="showModal = true"
                            class="relative w-16 h-16 rounded-xl overflow-hidden border border-[#DAE2FD] shadow-xs group shrink-0 focus:outline-none focus:ring-2 focus:ring-[#0051D5]">
                        <img src="{{ $photoUrl }}" alt="Bukti Foto" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    </button>
                @else
                    <div class="w-16 h-16 rounded-xl bg-[#EAEDFF] border border-[#DAE2FD] flex items-center justify-center text-[#64748B] shrink-0">
                        <span class="material-symbols-rounded text-2xl">person</span>
                    </div>
                @endif

                <!-- Metadata Info: Format Namatanggal -->
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] font-semibold text-[#64748B] tracking-wider uppercase">Lampiran Bukti Fisik</p>
                    <p class="text-[13px] font-semibold text-[#131B2E] truncate mt-1" title="{{ $photoFileName }}">
                        {{ $photoFileName }}
                    </p>
                </div>

                <!-- Fullscreen Button -->
                @if($photoUrl)
                    <button type="button" @click="showModal = true" title="Lihat Foto Layar Penuh"
                            class="w-9 h-9 rounded-xl bg-[#F2F3FF] border border-[#EAEDFF] text-[#64748B] hover:text-[#0051D5] hover:bg-[#EAEDFF] flex items-center justify-center transition-colors shrink-0">
                        <span class="material-symbols-rounded text-xl">fullscreen</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    <!-- 3. NEXT SCHEDULED ACTIVITY ALERT BANNER (Matching Tirtafield _buildNextActivityBanner) -->
    <section class="w-full px-3.5 py-3 rounded-xl bg-[#E2E7FF]/50 border border-[#DAE2FD] flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-[#DBE1FF] text-[#0051D5] flex items-center justify-center shrink-0">
            <span class="material-symbols-rounded text-lg">upcoming</span>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-medium text-[#64748B]">Sesi Aktivitas Berikutnya</p>
            <p class="text-[13px] font-bold text-[#131B2E] truncate mt-0.5">
                @if(isset($nextSchedule) && $nextSchedule)
                    {{ $nextSchedule->activity_name }} ({{ substr($nextSchedule->start_time, 0, 5) }} WIB)
                @else
                    Sesi Latihan Hari Ini Selesai
                @endif
            </p>
        </div>
        <span class="material-symbols-rounded text-[#C4C5D5] text-xl shrink-0">chevron_right</span>
    </section>

    <!-- 4. FIXED BOTTOM STICKY ACTION HUB (Matching Tirtafield _buildStickyFooter with 'Kembali ke Beranda') -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] z-40 px-4 py-3 bg-white/95 backdrop-blur-md border-t border-[#DAE2FD] shadow-[0_-4px_20px_rgba(15,23,42,0.06)]">
        <a href="{{ route('dashboard') }}"
           class="w-full h-[52px] bg-[#0051D5] hover:bg-[#00288E] active:scale-[0.98] text-white font-bold rounded-2xl flex items-center justify-center gap-2.5 shadow-md shadow-[#0051D5]/25 transition-all text-sm sm:text-base">
            <span class="material-symbols-rounded text-xl">home</span>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <!-- 5. FULLSCREEN IMAGE PREVIEW MODAL (Matching Tirtafield _showFullImageModal) -->
    @if($photoUrl)
        <div x-show="showModal" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @keydown.escape.window="showModal = false">
            <div @click.away="showModal = false" class="relative max-w-sm w-full bg-transparent">
                <button type="button" @click="showModal = false"
                        class="absolute -top-11 right-0 w-9 h-9 rounded-full bg-white/20 text-white hover:bg-white/30 flex items-center justify-center transition-colors">
                    <span class="material-symbols-rounded text-xl">close</span>
                </button>
                <div class="rounded-2xl overflow-hidden shadow-2xl bg-black border border-white/10">
                    <img src="{{ $photoUrl }}" alt="Foto Presensi Fullscreen" class="w-full max-h-[75vh] object-contain">
                </div>
                <p class="text-center text-xs text-white/80 mt-2 font-medium">
                    {{ $attendance->user->name }} • {{ $attendance->date->translatedFormat('d M Y') }} {{ $timeOnly }} WIB
                </p>
            </div>
        </div>
    @endif

</div>
@endsection
