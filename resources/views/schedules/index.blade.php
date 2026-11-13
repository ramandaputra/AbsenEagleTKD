@extends('layouts.app')

@section('title', 'Jadwal Latihan')

@section('content')
@php
    $oldCoachIds = old('coaches', ['']);
    $oldCoachIds = is_array($oldCoachIds) ? $oldCoachIds : [''];
@endphp

<div class="space-y-3.5" x-data="scheduleManager()">
    <section class="relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] p-4 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-5 -top-5 h-36 w-36 rounded-full bg-blue-500/20 blur-xl"></div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-cyan-400/10 blur-xl"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full border border-blue-300/40 bg-blue-500/25 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                    Latihan &amp; Penugasan
                </span>
                <h1 class="mt-1.5 text-sm font-extrabold tracking-tight">Jadwal Latihan</h1>
                <p class="mt-0.5 text-[11px] font-medium text-[#7DD3FC]">Daftar latihan dan asisten pelatih yang bertugas.</p>
            </div>

            @if(auth()->user()->isAdmin())
                <button type="button" @click="showAddModal = true"
                        class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-xl bg-white px-3 text-[11px] font-bold text-[#00288E] shadow-sm transition-colors hover:bg-[#EAEDFF]">
                    <span class="material-symbols-rounded text-base">add</span>
                    Atur Jadwal
                </button>
            @endif
        </div>
    </section>

    <section class="rounded-xl border border-[#DAE2FD]/70 bg-white p-3 shadow-2xs">
        <div class="flex items-center justify-between gap-2">
            <form method="GET" action="{{ route('schedules.index') }}" class="flex min-w-0 items-center gap-2">
                <label class="sr-only" for="schedule-month">Bulan</label>
                <select id="schedule-month" name="month" onchange="this.form.submit()"
                        class="min-w-0 rounded-lg border border-[#DAE2FD] bg-white px-2.5 py-2 text-[11px] font-semibold text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create($year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>

                <label class="sr-only" for="schedule-year">Tahun</label>
                <select id="schedule-year" name="year" onchange="this.form.submit()"
                        class="rounded-lg border border-[#DAE2FD] bg-white px-2.5 py-2 text-[11px] font-semibold text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    @foreach(range(2025, 2027) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>

            <span class="shrink-0 rounded-full bg-[#EAEDFF] px-2.5 py-1 text-[10px] font-bold text-[#0051D5]">
                {{ $schedules->count() }} jadwal
            </span>
        </div>
    </section>

    @if($schedules->isEmpty())
        <section class="rounded-2xl border border-[#DAE2FD] bg-white px-5 py-9 text-center shadow-xs">
            <span class="material-symbols-rounded text-4xl text-[#94A3B8]">event_busy</span>
            <h2 class="mt-2 text-xs font-bold text-[#131B2E]">Belum ada jadwal latihan</h2>
            <p class="mt-1 text-[11px] text-[#64748B]">Jadwal untuk bulan ini akan tampil di sini.</p>
            @if(auth()->user()->isAdmin())
                <button type="button" @click="showAddModal = true"
                        class="mt-4 inline-flex h-9 items-center gap-1.5 rounded-xl bg-[#0051D5] px-3.5 text-[11px] font-bold text-white transition-colors hover:bg-[#003EA8]">
                    <span class="material-symbols-rounded text-base">add</span>
                    Atur Jadwal
                </button>
            @endif
        </section>
    @else
        <section class="space-y-2.5">
            @foreach($schedules as $sched)
                <article class="space-y-3 rounded-2xl border border-[#DAE2FD] bg-white p-3.5 shadow-xs">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-[#EAEDFF] text-[#0051D5]">
                                <span class="text-[9px] font-bold uppercase leading-none">{{ $sched->date->translatedFormat('D') }}</span>
                                <span class="mt-1 text-base font-extrabold leading-none">{{ $sched->date->format('d') }}</span>
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-xs font-extrabold text-[#131B2E]">{{ $sched->activity_name }}</h2>
                                <p class="mt-1 flex items-center gap-1 text-[10px] text-[#64748B]">
                                    <span class="material-symbols-rounded text-sm text-[#0051D5]">calendar_today</span>
                                    {{ $sched->date->translatedFormat('d F Y') }}
                                </p>
                                <p class="mt-0.5 flex items-center gap-1 text-[10px] text-[#64748B]">
                                    <span class="material-symbols-rounded text-sm text-[#0051D5]">schedule</span>
                                    {{ substr((string) $sched->start_time, 0, 5) }}–{{ substr((string) $sched->end_time, 0, 5) }} WIB
                                </p>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('schedules.destroy', $sched) }}" method="POST" onsubmit="return confirm('Hapus jadwal latihan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus jadwal"
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-rose-100 text-rose-500 transition-colors hover:bg-rose-50">
                                    <span class="material-symbols-rounded text-base">delete</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-start gap-1.5 rounded-xl bg-[#F8FAFC] px-2.5 py-2 text-[10px] text-[#64748B]">
                        <span class="material-symbols-rounded text-sm text-[#0051D5]">location_on</span>
                        <span class="min-w-0 leading-snug">{{ $sched->location }}</span>
                    </div>

                    @if($sched->description)
                        <p class="rounded-xl border border-[#DAE2FD]/70 bg-[#F8FAFC] px-2.5 py-2 text-[10.5px] leading-snug text-[#475569]">
                            {{ $sched->description }}
                        </p>
                    @endif

                    <div class="space-y-2 rounded-xl border border-[#DAE2FD]/70 bg-[#F8FAFC] p-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-[#131B2E]">
                                <span class="material-symbols-rounded text-base text-[#0051D5]">groups</span>
                                Asisten Bertugas
                            </div>
                            <span class="truncate text-[9.5px] text-[#64748B]">
                                {{ $sched->dutySchedule ? $sched->dutySchedule->shift_name : 'Piket Utama' }}
                            </span>
                        </div>

                        @if($sched->dutySchedule && $sched->dutySchedule->assignments->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($sched->dutySchedule->assignments as $assign)
                                    <div class="flex items-center justify-between gap-2 rounded-lg border border-[#DAE2FD]/70 bg-white px-2.5 py-2">
                                        <div class="flex min-w-0 items-center gap-2">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $assign->user_id === auth()->id() ? 'bg-[#0051D5]' : 'bg-emerald-500' }}"></span>
                                            <span class="truncate text-[10.5px] font-semibold text-[#131B2E]">{{ $assign->user->name }}</span>
                                            @if($assign->user_id === auth()->id())
                                                <span class="shrink-0 rounded-full bg-[#EAEDFF] px-1.5 py-0.5 text-[9px] font-bold text-[#0051D5]">Tugas Anda</span>
                                            @endif
                                        </div>
                                        <span class="shrink-0 text-right text-[9.5px] text-[#64748B]">{{ $assign->role_task }}</span>
                                    </div>
                                    @if($assign->training_materials)
                                        <p class="px-2.5 text-[9.5px] italic text-[#64748B]">Materi: {{ $assign->training_materials }}</p>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="rounded-lg bg-white px-2.5 py-2 text-[10px] text-[#64748B]">Belum ada asisten yang ditugaskan.</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if(auth()->user()->isAdmin())
        <div x-show="showAddModal" x-cloak
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md space-y-4 rounded-2xl border border-[#DAE2FD] bg-white p-4 shadow-2xl"
                 @click.away="showAddModal = false">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#EAEDFF] text-[#0051D5]">
                            <span class="material-symbols-rounded">event_available</span>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-[#131B2E]">Atur Jadwal</h2>
                            <p class="mt-0.5 text-[10.5px] text-[#64748B]">Pilih tanggal dan asisten yang bertugas.</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddModal = false" title="Tutup"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#64748B] transition-colors hover:bg-[#F2F3FF]">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <form action="{{ route('schedules.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf

                    @if($errors->any())
                        <div class="space-y-1 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2">
                            @foreach($errors->all() as $error)
                                <p class="text-[10px] text-rose-700">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label for="schedule-date" class="mb-1 block text-[10.5px] font-semibold text-[#131B2E]">Tanggal Latihan</label>
                        <input id="schedule-date" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                               class="h-10 w-full rounded-xl border border-[#DAE2FD] bg-white px-3 text-xs text-[#131B2E] focus:border-[#0051D5] focus:outline-none focus:ring-2 focus:ring-[#0051D5]/10">
                    </div>

                    <div class="space-y-2 rounded-xl border border-[#DAE2FD]/70 bg-[#F8FAFC] p-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10.5px] font-semibold text-[#131B2E]">Asisten Pelatih</span>
                            <button type="button" @click="addCoachRow()"
                                    class="inline-flex items-center gap-1 rounded-lg bg-[#EAEDFF] px-2.5 py-1.5 text-[10px] font-bold text-[#0051D5] transition-colors hover:bg-[#DAE2FD]">
                                <span class="material-symbols-rounded text-sm">add</span>
                                Tambah
                            </button>
                        </div>

                        <template x-for="(row, index) in assignedRows" :key="index">
                            <div class="flex items-center gap-2">
                                <label class="sr-only" :for="'assistant-' + index">Pilih asisten pelatih</label>
                                <select :id="'assistant-' + index" name="coaches[]" x-model="row.coachId" required
                                        class="h-10 min-w-0 flex-1 rounded-xl border border-[#DAE2FD] bg-white px-3 text-xs text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                                    <option value="">Pilih nama asisten pelatih</option>
                                    @foreach($coaches as $coach)
                                        <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="removeCoachRow(index)" x-show="assignedRows.length > 1"
                                        class="flex h-9 shrink-0 items-center justify-center rounded-lg px-2 text-[10px] font-semibold text-rose-600 transition-colors hover:bg-rose-50">
                                    Hapus
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" @click="showAddModal = false"
                                class="h-10 flex-1 rounded-xl border border-[#DAE2FD] bg-white text-xs font-semibold text-[#64748B] transition-colors hover:bg-[#F8FAFC]">
                            Batal
                        </button>
                        <button type="submit"
                                class="h-10 flex-1 rounded-xl bg-[#0051D5] text-xs font-bold text-white transition-colors hover:bg-[#003EA8]">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function scheduleManager() {
    return {
        showAddModal: false,
        assignedRows: @js($oldCoachIds).map(coachId => ({ coachId: String(coachId) })),

        init() {
            if (@js($errors->any())) {
                this.showAddModal = true;
            }
        },

        addCoachRow() {
            this.assignedRows.push({ coachId: '' });
        },

        removeCoachRow(index) {
            this.assignedRows.splice(index, 1);
        }
    }
}
</script>
@endpush
@endsection
