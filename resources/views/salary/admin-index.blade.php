@extends('layouts.app')

@section('title', 'Manajemen Penggajian & Honor Asisten Pelatih')

@section('content')
<div class="space-y-4" x-data="payrollManager()">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="calculator" class="w-5 h-5 text-red-500"></i> Rekapitulasi Gaji Asisten Pelatih
            </h1>
            <p class="text-xs text-slate-400">
                Perhitungan total kehadiran dan nominal honor bulanan (tanpa payment gateway)
            </p>
        </div>

        <!-- Bulk Generate Button -->
        <form action="{{ route('salary.admin.generate') }}" method="POST">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" onclick="return confirm('Generate ulang perhitungan honor untuk seluruh asisten pelatih?')" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-amber-600 hover:opacity-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg transition-all">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Hitung & Kunci Payroll Semua</span>
            </button>
        </form>
    </div>

    <!-- Period Filter & Dojang Filter -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
        <form method="GET" action="{{ route('salary.admin.index') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <div>
                <label class="block text-slate-400 mb-1">Pilih Bulan</label>
                <select name="month" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ $monthNames[$m] ?? $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-400 mb-1">Tahun</label>
                <select name="year" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    @foreach(range(2025, 2027) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div>
                    <label class="block text-slate-400 mb-1">Filter Dojang</label>
                    <select name="dojang_id" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        <option value="">Semua Dojang</option>
                        @foreach($allDojangs as $dj)
                            <option value="{{ $dj->id }}" {{ $dojangId == $dj->id ? 'selected' : '' }}>{{ $dj->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex items-end gap-1 {{ !auth()->user()->isSuperAdmin() ? 'col-span-2' : '' }}">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold transition-colors">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Monthly Total Stats -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Sesi Kehadiran Tervalidasi
            </span>
            <div class="text-2xl font-black text-white mt-1">
                {{ $totalSessionsSum }} <span class="text-xs text-slate-400 font-normal">Sesi Latihan</span>
            </div>
            <p class="text-[10px] text-emerald-400 mt-0.5">
                Periode {{ $monthNames[$month] ?? $month }} {{ $year }}
            </p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Anggaran Honor Asisten
            </span>
            <div class="text-2xl font-black text-emerald-400 mt-1">
                Rp {{ number_format($totalPayrollSum, 0, ',', '.') }}
            </div>
            <p class="text-[10px] text-slate-400 mt-0.5">
                {{ count($payrollItems) }} Asisten Pelatih
            </p>
        </div>
    </div>

    <!-- Coach Payroll Cards List -->
    <div class="space-y-3">
        @foreach($payrollItems as $item)
            @php
                $coach = $item['coach'];
                $summary = $item['summary'];
                $payroll = $item['payroll'];
            @endphp
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 p-0.5 shrink-0">
                            <div class="w-full h-full rounded-[14px] bg-slate-900 flex items-center justify-center font-bold text-white text-base">
                                🥋
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xs font-bold text-white">{{ $coach->name }}</h3>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300">
                                    {{ $coach->belt_level }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $coach->dojang ? $coach->dojang->name : 'Dojang Pusat' }}
                            </p>
                            <p class="text-[10px] text-slate-500 font-mono">
                                {{ $coach->bank_name ?: 'BCA' }} • {{ $coach->bank_account_number ?: '-' }} a.n {{ $coach->bank_account_holder ?: $coach->name }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Column: Total Honor -->
                    <div class="text-right shrink-0">
                        @if($payroll)
                            <span class="text-[9px] px-2 py-0.5 rounded-full font-bold {{ $payroll->payment_status->badgeClass() }}">
                                {{ $payroll->payment_status->label() }}
                            </span>
                        @else
                            <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                Draft
                            </span>
                        @endif
                        <div class="text-base font-black text-emerald-400 mt-1">
                            Rp {{ number_format($summary['total_estimated_salary'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Calculation Formula Breakdown -->
                <div class="grid grid-cols-3 gap-2 p-2.5 rounded-2xl bg-slate-950/80 border border-slate-800/80 text-[11px]">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Kehadiran</span>
                        <strong class="text-white">{{ $summary['total_approved_sessions'] }} Sesi</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Tarif / Sesi</span>
                        <strong class="text-slate-300">Rp {{ number_format($summary['rate_per_session'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Honor Pokok</span>
                        <strong class="text-slate-200">Rp {{ number_format($summary['base_salary'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                @if($summary['estimated_bonus'] > 0 || $summary['estimated_deductions'] > 0)
                    <div class="flex items-center gap-3 text-[11px] px-1 text-slate-400">
                        @if($summary['estimated_bonus'] > 0)
                            <span class="text-emerald-400">+ Bonus: Rp {{ number_format($summary['estimated_bonus'], 0, ',', '.') }}</span>
                        @endif
                        @if($summary['estimated_deductions'] > 0)
                            <span class="text-rose-400">- Potongan: Rp {{ number_format($summary['estimated_deductions'], 0, ',', '.') }}</span>
                        @endif
                    </div>
                @endif

                <!-- Payment Details if Paid -->
                @if($payroll && $payroll->payment_status->value === 'paid')
                    <div class="p-2.5 rounded-xl bg-emerald-950/40 border border-emerald-700/40 text-[11px] text-emerald-300 flex items-center justify-between">
                        <span>✓ Lunas: {{ $payroll->payment_date ? $payroll->payment_date->translatedFormat('d M Y') : '-' }} via {{ $payroll->payment_method->label() }}</span>
                        @if($payroll->payment_notes)
                            <span class="italic text-slate-300 text-[10px]">"{{ $payroll->payment_notes }}"</span>
                        @endif
                    </div>
                @endif

                <!-- Action Controls -->
                <div class="pt-2 border-t border-slate-800 flex items-center justify-between gap-2 text-xs">
                    <a href="{{ route('salary.admin.slip', ['coach' => $coach->id, 'month' => $month, 'year' => $year]) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold flex items-center gap-1.5 transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Slip Gaji</span>
                    </a>

                    <div class="flex items-center gap-1.5">
                        <!-- Edit Bonus & Deductions Modal Button -->
                        <button type="button" @click="openAdjustmentModal({{ $coach->id }}, '{{ addslashes($coach->name) }}', {{ $summary['estimated_bonus'] }}, {{ $summary['estimated_deductions'] }}, '{{ addslashes($payroll?->payment_notes ?? '') }}')" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 font-semibold flex items-center gap-1 transition-colors">
                            <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                            <span>Bonus/Potongan</span>
                        </button>

                        <!-- Mark Status Modal Button -->
                        @if($payroll)
                            <button type="button" @click="openStatusModal({{ $payroll->id }}, '{{ addslashes($coach->name) }}', '{{ $payroll->payment_status->value }}', '{{ $payroll->payment_method->value }}', '{{ $payroll->payment_date?->format('Y-m-d') ?? date('Y-m-d') }}', '{{ addslashes($payroll->payment_notes ?? '') }}')" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold flex items-center gap-1 transition-colors">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>Status Bayar</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal: Edit Bonus & Deductions -->
    <div x-show="showAdjustmentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-sm shadow-2xl space-y-4" @click.away="showAdjustmentModal = false">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
                Sesuaikan Honor: <span x-text="selectedCoachName"></span>
            </h3>

            <form :action="'/admin/salary/' + selectedCoachId + '/adjustment'" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

                <div>
                    <label class="block text-slate-400 mb-1">Tambahan Bonus / Tunjangan (Rp)</label>
                    <input type="number" step="5000" name="bonus_allowance" x-model="modalBonus" required
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Potongan (Rp)</label>
                    <input type="number" step="5000" name="deductions" x-model="modalDeductions" required
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Catatan Keterangan</label>
                    <input type="text" name="payment_notes" x-model="modalNotes" placeholder="Contoh: Bonus melatih kejuaraan daerah"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:outline-none focus:border-red-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showAdjustmentModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Update Payment Status -->
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-sm shadow-2xl space-y-4" @click.away="showStatusModal = false">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                Status Pembayaran Honor
            </h3>
            <p class="text-xs text-slate-400">Pelatih: <strong class="text-white" x-text="selectedCoachName"></strong></p>

            <form :action="'/admin/salary/' + selectedPayrollId + '/status'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">Status Pembayaran</label>
                    <select name="payment_status" x-model="modalStatus" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        <option value="draft">Draft Perhitungan</option>
                        <option value="approved">Disetujui Pimpinan</option>
                        <option value="paid">Lunas / Telah Dibayar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Metode Pembayaran (Tanpa Gateway)</label>
                    <select name="payment_method" x-model="modalMethod" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Tunai / Cash</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" x-model="modalDate"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Catatan Bukti / Keterangan</label>
                    <input type="text" name="payment_notes" x-model="modalStatusNotes" placeholder="Contoh: Ditransfer via BCA no ref #882910"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:outline-none focus:border-red-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showStatusModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
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

