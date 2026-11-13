@extends('layouts.app')

@section('title', 'Rekap Hadir & Gaji Bulanan')

@section('content')
<div class="space-y-3.5" x-data="payrollManager()">
    <section class="rounded-2xl p-4 text-white shadow-lg relative overflow-hidden bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] border border-white/15">
        <div class="absolute -right-5 -top-5 w-32 h-32 rounded-full bg-blue-500/20 blur-xl pointer-events-none"></div>
        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-500/25 text-blue-100 border border-blue-300/40">Rekap Bulanan</span>
                <h1 class="text-sm font-extrabold tracking-tight mt-1.5">Rekap Hadir &amp; Gaji</h1>
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
            <h2 class="text-[13.5px] font-bold text-[#131B2E] tracking-tight">Daftar Rekap Pelatih</h2>
            <span class="text-[10px] font-medium text-[#64748B]">{{ count($payrollItems) }} pelatih</span>
        </div>

        <div class="space-y-2">
            @if($payrollItems === [])
                <div class="bg-white border border-[#DAE2FD]/70 rounded-xl p-7 text-center shadow-2xs">
                    <span class="material-symbols-rounded text-3xl text-[#94A3B8]">group_off</span>
                    <h3 class="text-sm font-bold text-[#131B2E] mt-2">Belum ada asisten pelatih aktif</h3>
                    <p class="text-xs text-[#64748B] mt-1">Ubah filter dojang atau aktifkan data asisten pelatih untuk melihat rekap.</p>
                </div>
            @endif

            @foreach($payrollItems as $item)
                @php
                    $coach = $item['coach'];
                    $summary = $item['summary'];
                    $payroll = $item['payroll'];
                @endphp
                <article class="bg-white border border-[#DAE2FD]/70 rounded-xl p-3 shadow-2xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-9 h-9 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-lg">person</span>
                        </span>
                        <h3 class="min-w-0 text-xs font-bold text-[#131B2E] leading-snug">{{ $coach->name }}</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-3">
                        <div class="rounded-lg bg-[#F8FAFC] border border-[#DAE2FD]/70 px-2.5 py-2">
                            <span class="block text-[9px] font-semibold uppercase tracking-wide text-[#64748B]">Jumlah Hadir</span>
                            <strong class="block text-sm text-[#0051D5] mt-0.5">{{ $summary['total_approved_sessions'] }} <span class="text-[10px] font-medium text-[#64748B]">hari</span></strong>
                        </div>
                        <div class="rounded-lg bg-emerald-50 border border-emerald-100 px-2.5 py-2">
                            <span class="block text-[9px] font-semibold uppercase tracking-wide text-emerald-800/70">Total Gaji</span>
                            <strong class="block text-sm text-emerald-700 mt-0.5">Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <p class="text-[9px] text-[#64748B] mt-1.5">
                        Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }} per hari × {{ $summary['total_approved_sessions'] }} hari hadir
                    </p>

                    <div class="pt-2 mt-2 border-t border-[#DAE2FD]/70 flex items-center justify-between gap-2 text-[10px]">
                        <a href="{{ route('salary.admin.slip', ['coach' => $coach->id, 'month' => $month, 'year' => $year]) }}" class="px-2.5 py-1.5 rounded-lg bg-[#F2F3FF] hover:bg-[#EAEDFF] text-[#00288E] font-semibold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-rounded text-sm">print</span>
                            <span>Slip Gaji</span>
                        </a>

                        <div class="flex items-center gap-1">
                            <button type="button" @click="openAdjustmentModal({{ $coach->id }}, '{{ addslashes($coach->name) }}', {{ $summary['estimated_bonus'] }}, {{ $summary['estimated_deductions'] }}, '{{ addslashes($payroll?->payment_notes ?? '') }}')" class="px-2 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold flex items-center gap-1 transition-colors">
                                <span class="material-symbols-rounded text-sm">tune</span>
                                <span>Penyesuaian</span>
                            </button>

                            @if($payroll)
                                <button type="button" @click="openStatusModal({{ $payroll->id }}, '{{ addslashes($coach->name) }}', '{{ $payroll->payment_status->value }}', '{{ $payroll->payment_method->value }}', '{{ $payroll->payment_date?->format('Y-m-d') ?? date('Y-m-d') }}', '{{ addslashes($payroll->payment_notes ?? '') }}')" class="px-2 py-1.5 rounded-lg bg-[#00288E] hover:bg-[#0051D5] text-white font-semibold flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-rounded text-sm">payments</span>
                                    <span>Status</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
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

    <!-- Modal: Update Payment Status -->
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white border border-[#DAE2FD] rounded-2xl p-5 w-full max-w-sm shadow-2xl space-y-4" @click.away="showStatusModal = false">
            <h3 class="text-sm font-bold text-[#131B2E] flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-700"></i>
                Status Pembayaran Honor
            </h3>
            <p class="text-xs text-[#64748B]">Pelatih: <strong class="text-[#131B2E]" x-text="selectedCoachName"></strong></p>

            <form :action="'/admin/salary/' + selectedPayrollId + '/status'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[#64748B] mb-1">Status Pembayaran</label>
                    <select name="payment_status" x-model="modalStatus" required class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        <option value="draft">Draft Perhitungan</option>
                        <option value="approved">Disetujui Pimpinan</option>
                        <option value="paid">Lunas / Telah Dibayar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[#64748B] mb-1">Metode Pembayaran (Tanpa Gateway)</label>
                    <select name="payment_method" x-model="modalMethod" required class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Tunai / Cash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[#64748B] mb-1">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" x-model="modalDate"
                        class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                </div>

                <div>
                    <label class="block text-[#64748B] mb-1">Catatan Bukti / Keterangan</label>
                    <input type="text" name="payment_notes" x-model="modalStatusNotes" placeholder="Contoh: Ditransfer via BCA no ref #882910"
                        class="w-full px-3 py-2 rounded-xl bg-[#F8FAFC] border border-[#DAE2FD] text-[#131B2E] text-xs focus:outline-none focus:border-[#0051D5]">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showStatusModal = false" class="flex-1 py-2 rounded-xl bg-[#F2F3FF] text-[#334155] font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-[#00288E] hover:bg-[#0051D5] text-white font-bold">
                        Simpan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function payrollManager() {
    return {
        showAdjustmentModal: false,
        showStatusModal: false,
        selectedCoachId: null,
        selectedCoachName: '',
        selectedPayrollId: null,
        modalBonus: 0,
        modalDeductions: 0,
        modalNotes: '',
        modalStatus: 'draft',
        modalMethod: 'transfer',
        modalDate: '{{ date('Y-m-d') }}',
        modalStatusNotes: '',

        openAdjustmentModal(coachId, coachName, bonus, deductions, notes) {
            this.selectedCoachId = coachId;
            this.selectedCoachName = coachName;
            this.modalBonus = bonus;
            this.modalDeductions = deductions;
            this.modalNotes = notes;
            this.showAdjustmentModal = true;
        },

        openStatusModal(payrollId, coachName, status, method, date, notes) {
            this.selectedPayrollId = payrollId;
            this.selectedCoachName = coachName;
            this.modalStatus = status;
            this.modalMethod = method;
            this.modalDate = date;
            this.modalStatusNotes = notes;
            this.showStatusModal = true;
        }
    }
}
</script>
@endpush
@endsection

