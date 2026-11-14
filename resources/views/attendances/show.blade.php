@extends('layouts.app')

@section('title', 'Detail Absensi')

@section('content')
<div>
    <section class="overflow-hidden rounded-2xl border border-[#DAE2FD] bg-white shadow-xs">
        @forelse($attendance->photos as $photo)
            <a href="{{ asset($photo->photo_path) }}" target="_blank" rel="noopener noreferrer"
               aria-label="Buka foto kiriman ukuran asli"
               class="block p-2.5 {{ !$loop->first ? 'border-t border-[#DAE2FD]/70' : '' }}">
                <div class="mx-auto aspect-[3/4] w-full max-w-[360px] overflow-hidden rounded-xl bg-[#F8FAFC]">
                    <img src="{{ asset($photo->photo_path) }}"
                         alt="Foto kiriman {{ $attendance->date->format('d-m-Y') }}"
                         loading="lazy" decoding="async"
                         class="h-full w-full object-contain">
                </div>
            </a>
        @empty
            <p class="border-b border-[#DAE2FD]/70 px-3.5 py-3 text-[10px] text-[#64748B]">Belum ada foto yang dikirim.</p>
        @endforelse

        <div class="space-y-3.5 p-3.5">
            <div class="flex items-start gap-2.5">
                <span class="material-symbols-rounded mt-0.5 text-base text-[#0051D5]">calendar_today</span>
                <div>
                    <p class="text-[9.5px] font-medium text-[#64748B]">Tanggal</p>
                    <p class="mt-0.5 text-[10.5px] font-semibold text-[#131B2E]">{{ $attendance->date->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <span class="material-symbols-rounded mt-0.5 text-base text-[#0051D5]">schedule</span>
                <div>
                    <p class="text-[9.5px] font-medium text-[#64748B]">Waktu</p>
                    <p class="mt-0.5 font-mono text-[10.5px] font-semibold text-[#131B2E]">{{ substr((string) $attendance->time, 0, 5) }} WIB</p>
                </div>
            </div>

            <div class="flex items-start gap-2.5">
                <span class="material-symbols-rounded mt-0.5 text-base text-[#0051D5]">sports_martial_arts</span>
                <div class="min-w-0">
                    <p class="text-[9.5px] font-medium text-[#64748B]">Aktivitas Latihan</p>
                    <p class="mt-0.5 text-[10.5px] font-semibold text-[#131B2E]">{{ $attendance->trainingSchedule?->activity_name ?? 'Latihan Mandiri' }}</p>
                </div>
            </div>

            @if(session('success'))
                <p class="border-t border-[#DAE2FD]/70 pt-2.5 text-[10px] font-semibold text-emerald-700">{{ session('success') }}</p>
            @endif
        </div>
    </section>
</div>
@endsection
