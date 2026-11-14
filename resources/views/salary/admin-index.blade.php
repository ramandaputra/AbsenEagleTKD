@extends('layouts.app')

@section('title', 'Rekap Gaji & Tarif Pertemuan')

@section('content')
@php
    $monthStartDate = \Carbon\Carbon::create($year, $month, 1);
    $calendarOffset = $monthStartDate->dayOfWeekIso - 1;
    $calendarDays = array_merge(
        array_fill(0, $calendarOffset, null),
        range(1, $monthStartDate->daysInMonth)
    );
    $calendarDays = array_pad($calendarDays, (int) ceil(count($calendarDays) / 7) * 7, null);
@endphp

<div class="space-y-3.5" x-data="payrollManager()">
    <section class="rounded-2xl p-4 text-white shadow-lg relative overflow-hidden bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] border border-white/15">
        <div class="absolute -right-5 -top-5 w-32 h-32 rounded-full bg-blue-500/20 blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500/25 text-blue-100 border border-blue-300/40">Rekap &amp; Tarif</span>
                <h1 class="text-sm font-extrabold tracking-tight mt-1.5">Rekap Hadir, Gaji &amp; Tarif Pertemuan</h1>
                <p class="text-[11px] font-semibold text-[#7DD3FC] mt-0.5">{{ $monthNames[$month] ?? $month }} {{ $year }}</p>
            </div>
            <form action="{{ route('salary.admin.generate') }}" method="POST" class="relative z-10 shrink-0">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                @if(auth()->user()->isSuperAdmin())
                    <input type="hidden" name="dojang_id" value="{{ $dojangId }}">
                @endif
                <button type="submit" onclick="return confirm('Hitung ulang honor untuk asisten pelatih pada periode dan filter ini?')" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-[10px] flex items-center justify-center gap-1.5 transition-colors">
                    <span class="material-symbols-rounded text-base">calculate</span>
                    <span>Hitung Gaji</span>
                </button>
            </form>
        </div>
        <div class="relative z-10 mt-3 pt-2.5 border-t border-white/15 flex items-center gap-1.5 text-[10.5px] text-white/80">
            <span class="material-symbols-rounded text-sm text-[#7DD3FC]">payments</span>
            <span>Gaji per hari × jumlah hadir</span>
        </div>
    </section>

    <section class="bg-white rounded-xl p-3 border border-[#DAE2FD]/70 shadow-2xs">
        <form method="GET" action="{{ route('salary.admin.index') }}" class="grid grid-cols-2 gap-2 text-[10px]">
            <div>
                <label class="block text-[#64748B] font-semibold mb-1">Bulan</label>
                <select name="month" class="w-full py-2 px-2.5 rounded-lg bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $monthNames[$m] ?? $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[#64748B] font-semibold mb-1">Tahun</label>
                <select name="year" class="w-full py-2 px-2.5 rounded-lg bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                    @foreach(range(2025, 2027) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isSuperAdmin())
                <div class="col-span-2">
                    <label class="block text-[#64748B] font-semibold mb-1">Dojang</label>
                    <select name="dojang_id" class="w-full py-2 px-2.5 rounded-lg bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        <option value="">Semua Dojang</option>
                        @foreach($allDojangs as $dj)
                            <option value="{{ $dj->id }}" {{ $dojangId == $dj->id ? 'selected' : '' }}>{{ $dj->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit" class="col-span-2 py-2 rounded-lg bg-[#00288E] hover:bg-[#0051D5] text-white font-bold transition-colors">Terapkan Filter</button>
        </form>
    </section>

    <section class="bg-white rounded-xl p-3.5 border border-[#DAE2FD]/70 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-semibold text-[#64748B]">Total Gaji yang Harus Dibayarkan</span>
            <span class="material-symbols-rounded text-base text-emerald-700">payments</span>
        </div>
        <div class="text-lg font-extrabold text-emerald-700 mt-1">Rp {{ number_format($totalPayrollSum, 0, ',', '.') }}</div>
        <p class="text-[9px] text-[#64748B] mt-0.5">{{ count($payrollItems) }} asisten pelatih · {{ $monthNames[$month] ?? $month }} {{ $year }}</p>
    </section>

    <section class="space-y-2">
        <div class="flex items-center justify-between px-0.5">
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Daftar Rekap Asisten Pelatih</h2>
            <span class="text-[10px] font-medium text-[#64748B]">{{ count($payrollItems) }} asisten</span>
        </div>

        <div class="space-y-2">
            @error('rate_per_session')
                <p class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-[10.5px] font-semibold text-rose-700">{{ $message }}</p>
            @enderror

            @if($payrollItems === [])
                <div class="bg-white border border-[#DAE2FD]/70 rounded-xl p-7 text-center shadow-2xs">
                    <span class="material-symbols-rounded text-3xl text-[#94A3B8]">group_off</span>
                    <h3 class="text-sm font-bold text-[#131B2E] mt-2">Belum ada asisten pelatih aktif</h3>
                    <p class="text-xs text-[#64748B] mt-1">Ubah filter dojang atau aktifkan data asisten pelatih untuk melihat rekap.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-[#DAE2FD]/70 bg-white shadow-2xs divide-y divide-[#DAE2FD]/70">
                    @foreach($payrollItems as $item)
                        @php
                            $coach = $item['coach'];
                            $summary = $item['summary'];
                            $payroll = $item['payroll'];
                            $presentAttendances = $summary['attendances']
                                ->where('status', 'Hadir')
                                ->sortBy(fn ($attendance) => $attendance->date->format('Y-m-d').' '.(string) $attendance->time)
                                ->map(fn ($attendance) => [
                                    'day' => (int) $attendance->date->day,
                                    'date' => $attendance->date->format('d/m/Y'),
                                    'time' => substr((string) $attendance->time, 0, 5),
                                    'activity' => $attendance->trainingSchedule?->activity_name ?? 'Latihan',
                                ])
                                ->values();
                        @endphp
                        <details class="group px-3 py-2.5" {{ old('coach_id') == $coach->id ? 'open' : '' }}>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-2.5">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAEDFF] text-[#0051D5]">
                                        <span class="material-symbols-rounded text-base">person</span>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-xs font-bold text-[#131B2E]">{{ $coach->name }}</h3>
                                        <p class="mt-0.5 text-[9px] text-[#64748B]">{{ $summary['total_approved_sessions'] }} hadir · Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }} per pertemuan</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="block text-[8px] font-semibold uppercase text-[#64748B]">Total gaji</span>
                                    <strong class="text-xs font-extrabold text-emerald-700">Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}</strong>
                                </div>
                                <span class="material-symbols-rounded shrink-0 text-lg text-[#64748B] transition-transform group-open:rotate-180">expand_more</span>
                            </summary>

                            <div class="mt-2.5 border-t border-[#DAE2FD]/70 pt-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
                                        <button type="button"
                                                data-coach-name="{{ $coach->name }}"
                                                @click="openRateModal({{ $coach->id }}, $event.currentTarget.dataset.coachName, {{ old('coach_id') == $coach->id ? (float) old('rate_per_session', $coach->rate_per_session) : (int) $coach->rate_per_session }})"
                                                class="inline-flex items-center gap-1 rounded-lg bg-[#EAEDFF] px-2.5 py-1.5 text-[9px] font-semibold text-[#00288E] transition-colors hover:bg-[#DAE2FD]">
                                            <span class="material-symbols-rounded text-sm">edit</span>
                                            Ubah Tarif
                                        </button>
                                        <button type="button" @click="openAdjustmentModal({{ $coach->id }}, '{{ addslashes($coach->name) }}', {{ $summary['estimated_bonus'] }}, {{ $summary['estimated_deductions'] }}, '{{ addslashes($payroll?->payment_notes ?? '') }}')" class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1.5 text-[9px] font-semibold text-amber-800 transition-colors hover:bg-amber-100">
                                            <span class="material-symbols-rounded text-sm">tune</span>
                                            Penyesuaian
                                        </button>
                                        <button type="button"
                                                data-coach-name="{{ $coach->name }}"
                                                data-attendances="{{ $presentAttendances->toJson() }}"
                                                data-attended-days="{{ $presentAttendances->pluck('day')->unique()->values()->toJson() }}"
                                                @click="openAttendanceModal($event.currentTarget.dataset.coachName, JSON.parse($event.currentTarget.dataset.attendances), JSON.parse($event.currentTarget.dataset.attendedDays))"
                                                class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1.5 text-[9px] font-semibold text-blue-800 transition-colors hover:bg-blue-100">
                                            <span class="material-symbols-rounded text-sm">event_note</span>
                                            Detail Hadir
                                        </button>
                                    </div>
                                    <a href="{{ route('salary.admin.slip', ['coach' => $coach->id, 'month' => $month, 'year' => $year, 'dojang_id' => $dojangId]) }}" class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-lg bg-[#00288E] px-2.5 py-1.5 text-[9px] font-semibold text-white transition-colors hover:bg-[#0051D5]">
                                        <span class="material-symbols-rounded text-sm">print</span>
                                        Slip Gaji
                                    </a>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <!-- Modal: Edit Rate per Session -->
    <div x-show="showRateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="w-full max-w-sm space-y-4 rounded-2xl border border-[#DAE2FD] bg-white p-5 shadow-2xl" @click.away="showRateModal = false">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-[#131B2E]">Ubah Tarif per Pertemuan</h3>
                    <p class="mt-1 text-xs text-[#64748B]">Asisten: <strong class="text-[#131B2E]" x-text="selectedRateCoachName"></strong></p>
                </div>
                <button type="button" @click="showRateModal = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F2F3FF]" title="Tutup">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <form :action="rateFormAction" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <input type="hidden" name="coach_id" :value="selectedRateCoachId">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                @if(auth()->user()->isSuperAdmin())
                    <input type="hidden" name="dojang_id" value="{{ $dojangId }}">
                @endif

                <div>
                    <label for="modal-rate-per-session" class="mb-1 block text-xs font-semibold text-[#64748B]">Tarif per pertemuan (Rp)</label>
                    <input id="modal-rate-per-session" type="number" name="rate_per_session" min="0" step="1" required x-model.number="modalRatePerSession"
                           class="h-11 w-full rounded-xl border border-[#DAE2FD] px-3 text-sm font-semibold text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="button" @click="showRateModal = false" class="h-10 flex-1 rounded-xl bg-[#F2F3FF] text-xs font-semibold text-[#334155]">Batal</button>
                    <button type="submit" class="h-10 flex-1 rounded-xl bg-[#0051D5] text-xs font-bold text-white hover:bg-[#003EA8]">Simpan Tarif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Bonus & Deductions -->
    <div x-show="showAdjustmentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white border border-[#DAE2FD] rounded-2xl p-5 w-full max-w-sm shadow-2xl space-y-4" @click.away="showAdjustmentModal = false">
            <h3 class="text-sm font-bold text-[#131B2E] flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-amber-600"></i>
                Sesuaikan Honor: <span x-text="selectedCoachName"></span>
            </h3>

            <form :action="'/admin/salary/' + selectedCoachId + '/adjustment'" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div>
                    <label class="block text-[#64748B] mb-1">Tambahan Bonus / Tunjangan (Rp)</label>
                    <input type="number" step="5000" name="bonus_allowance" x-model="modalBonus" required
                        class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] text-sm focus:outline-none focus:border-[#0051D5]">
                </div>

                <div>
                    <label class="block text-[#64748B] mb-1">Potongan (Rp)</label>
                    <input type="number" step="5000" name="deductions" x-model="modalDeductions" required
                        class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] text-sm focus:outline-none focus:border-[#0051D5]">
                </div>

                <div>
                    <label class="block text-[#64748B] mb-1">Catatan Keterangan</label>
                    <input type="text" name="payment_notes" x-model="modalNotes" placeholder="Contoh: Bonus melatih kejuaraan daerah"
                        class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] text-xs focus:outline-none focus:border-[#0051D5]">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showAdjustmentModal = false" class="flex-1 py-2 rounded-xl bg-[#F2F3FF] text-[#334155] font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-[#00288E] hover:bg-[#0051D5] text-white font-bold">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAttendanceModal" x-cloak x-transition @keydown.escape.window="showAttendanceModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-2xl border border-[#DAE2FD] bg-white shadow-2xl" @click.away="showAttendanceModal = false">
            <div class="flex items-start justify-between gap-3 border-b border-[#DAE2FD]/70 p-4">
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-[#131B2E]">Rekap Hadir {{ $monthNames[$month] ?? $month }} {{ $year }}</h3>
                    <p class="mt-1 truncate text-xs text-[#64748B]" x-text="selectedAttendanceCoachName"></p>
                </div>
                <button type="button" @click="showAttendanceModal = false" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[#64748B] hover:bg-[#F2F3FF]" aria-label="Tutup">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4">
                <section class="space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-bold text-[#131B2E]">Kalender {{ $monthNames[$month] ?? $month }} {{ $year }}</h4>
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-[#0051D5]">
                            <span class="h-2 w-2 rounded-full bg-[#0051D5]"></span>
                            Hadir
                        </span>
                    </div>
                    <div class="grid grid-cols-7 gap-1.5 text-center">
                        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                            <span class="py-1 text-[9px] font-semibold uppercase text-[#64748B]">{{ $weekday }}</span>
                        @endforeach
                        @foreach($calendarDays as $calendarDay)
                            @if($calendarDay)
                                <div class="flex aspect-square flex-col items-center justify-center rounded-lg text-[11px] font-semibold"
                                     :class="selectedAttendanceDays.includes({{ $calendarDay }}) ? 'bg-[#0051D5] text-white ring-1 ring-[#0051D5]' : 'bg-[#F8FAFC] text-[#64748B]'">
                                    <span>{{ $calendarDay }}</span>
                                    <span x-show="selectedAttendanceDays.includes({{ $calendarDay }})" class="mt-0.5 h-1 w-1 rounded-full bg-white"></span>
                                </div>
                            @else
                                <span class="aspect-square" aria-hidden="true"></span>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="space-y-2 border-t border-[#DAE2FD]/70 pt-3">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-xs font-bold text-[#131B2E]">Rincian Sesi</h4>
                        <span class="text-[10px] text-[#64748B]" x-text="selectedAttendances.length + ' sesi hadir'"></span>
                    </div>
                    <p x-show="selectedAttendances.length === 0" class="rounded-xl bg-[#F8FAFC] px-3 py-4 text-center text-[11px] text-[#64748B]">Belum ada kehadiran pada bulan ini.</p>
                    <template x-for="(attendance, index) in selectedAttendances" :key="index">
                        <article class="flex items-center gap-3 rounded-xl border border-[#DAE2FD]/70 bg-[#F8FAFC] p-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                <span class="material-symbols-rounded text-lg">event_available</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-[#131B2E]" x-text="attendance.date"></p>
                                <p class="mt-0.5 truncate text-[10px] text-[#64748B]" x-text="attendance.activity"></p>
                            </div>
                            <span class="shrink-0 text-xs font-semibold text-[#0051D5]" x-text="attendance.time"></span>
                        </article>
                    </template>
                </section>
            </div>

            <div class="flex justify-end border-t border-[#DAE2FD]/70 p-4">
                <button type="button" @click="showAttendanceModal = false" class="rounded-xl bg-[#00288E] px-4 py-2 text-xs font-bold text-white hover:bg-[#0051D5]">Tutup</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function payrollManager() {
    return {
        showRateModal: false,
        showAdjustmentModal: false,
        showAttendanceModal: false,
        selectedRateCoachId: null,
        selectedRateCoachName: '',
        modalRatePerSession: 0,
        rateFormAction: '',
        selectedCoachId: null,
        selectedCoachName: '',
        modalBonus: 0,
        modalDeductions: 0,
        modalNotes: '',
        selectedAttendanceCoachName: '',
        selectedAttendances: [],
        selectedAttendanceDays: [],

        openRateModal(coachId, coachName, ratePerSession) {
            this.selectedRateCoachId = coachId;
            this.selectedRateCoachName = coachName;
            this.modalRatePerSession = ratePerSession;
            this.rateFormAction = '{{ route('salary.admin.coach-rate', ['coach' => '__COACH_ID__']) }}'.replace('__COACH_ID__', coachId);
            this.showRateModal = true;
        },

        openAdjustmentModal(coachId, coachName, bonus, deductions, notes) {
            this.selectedCoachId = coachId;
            this.selectedCoachName = coachName;
            this.modalBonus = bonus;
            this.modalDeductions = deductions;
            this.modalNotes = notes;
            this.showAdjustmentModal = true;
        },

        openAttendanceModal(coachName, attendances, attendedDays) {
            this.selectedAttendanceCoachName = coachName;
            this.selectedAttendances = attendances;
            this.selectedAttendanceDays = attendedDays;
            this.showAttendanceModal = true;
        }

    }
}
</script>
@endpush
@endsection

