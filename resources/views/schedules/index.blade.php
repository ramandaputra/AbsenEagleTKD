@extends('layouts.app')

@section('title', 'Jadwal Latihan & Piket Mengajar')

@section('content')
<div class="space-y-4" x-data="scheduleManager()">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-red-500"></i> Jadwal Latihan & Piket Mengajar
            </h1>
            <p class="text-xs text-slate-400">Jadwal kegiatan latihan taekwondo terintegrasi dengan penugasan asisten pelatih</p>
        </div>

        @if(auth()->user()->isAdmin())
            <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-gradient-to-r from-red-600 to-amber-600 hover:opacity-95 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Jadwal & Piket</span>
            </button>
        @endif
    </div>

    <!-- Month Filter & Mode Tabs -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex items-center justify-between">
        <form method="GET" action="{{ route('schedules.index') }}" class="flex items-center gap-2 text-xs">
            <select name="month" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl bg-slate-950 border border-slate-700 text-white font-semibold focus:outline-none focus:border-red-500">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl bg-slate-950 border border-slate-700 text-white font-semibold focus:outline-none focus:border-red-500">
                @foreach(range(2025, 2027) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>

        <span class="text-xs text-slate-400 font-semibold">
            Total {{ $schedules->count() }} Kegiatan
        </span>
    </div>

    <!-- Schedules List -->
    @if($schedules->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center">
            <i data-lucide="calendar-x" class="w-12 h-12 text-slate-600 mx-auto mb-3"></i>
            <h3 class="text-sm font-bold text-white">Belum ada jadwal latihan</h3>
            <p class="text-xs text-slate-400 mt-1">Belum ada jadwal kegiatan latihan yang dibuat untuk bulan ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($schedules as $sched)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-red-600 to-amber-500 p-0.5 shrink-0">
                                <div class="w-full h-full rounded-[14px] bg-slate-950 flex flex-col items-center justify-center font-bold">
                                    <span class="text-[9px] uppercase text-red-400">{{ $sched->date->translatedFormat('D') }}</span>
                                    <span class="text-xs text-white">{{ $sched->date->format('d') }}</span>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-sm font-extrabold text-white">{{ $sched->activity_name }}</h2>
                                <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-400"></i>
                                    {{ substr($sched->start_time, 0, 5) }} - {{ substr($sched->end_time, 0, 5) }} WIB
                                    • <i data-lucide="map-pin" class="w-3.5 h-3.5 text-red-400"></i> {{ $sched->location }}
                                </p>
                            </div>
                        </div>

                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('schedules.destroy', $sched) }}" method="POST" onsubmit="return confirm('Hapus jadwal latihan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-xl bg-slate-950 hover:bg-rose-950/60 border border-slate-800 hover:border-rose-700/60 flex items-center justify-center text-slate-500 hover:text-rose-400 transition-colors">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($sched->description)
                        <div class="text-[11px] text-slate-300 italic px-3 py-1.5 rounded-xl bg-slate-950/60 border border-slate-800/60">
                            {{ $sched->description }}
                        </div>
                    @endif

                    <!-- Petugas Piket Mengajar (Duty Assignments) -->
                    <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800/80">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1">
                                <i data-lucide="users" class="w-3.5 h-3.5 text-amber-400"></i> Petugas Piket Mengajar:
                            </span>
                            <span class="text-[10px] text-slate-500">
                                {{ $sched->dutySchedule ? $sched->dutySchedule->shift_name : 'Piket Utama' }}
                            </span>
                        </div>

                        @if($sched->dutySchedule && $sched->dutySchedule->assignments->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($sched->dutySchedule->assignments as $assign)
                                    <div class="p-2 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $assign->user_id === auth()->id() ? 'bg-red-500 animate-ping' : 'bg-emerald-500' }}"></span>
                                            <strong class="text-white">{{ $assign->user->name }}</strong>
                                            @if($assign->user_id === auth()->id())
                                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-red-950 text-red-300 border border-red-800">Tugas Anda</span>
                                            @endif
                                            <span class="text-[10px] text-slate-400">({{ $assign->role_task }})</span>
                                        </div>
                                        @if($assign->training_materials)
                                            <span class="text-[10px] text-slate-400 truncate max-w-[150px] text-right italic">
                                                "{{ $assign->training_materials }}"
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-[11px] text-slate-500 italic">Belum ada asisten pelatih yang ditugaskan piket.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal: Tambah Jadwal & Piket Mengajar -->
    @if(auth()->user()->isAdmin())
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-lg shadow-2xl space-y-4 my-8" @click.away="showAddModal = false">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="calendar-plus" class="w-4 h-4 text-red-500"></i>
                    Tambah Jadwal Latihan & Penugasan Piket
                </h3>

                <form action="{{ route('schedules.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf

                    <div>
                        <label class="block text-slate-400 mb-1">Nama Kegiatan Latihan</label>
                        <input type="text" name="activity_name" required placeholder="Contoh: Kelas Reguler Poomsae & Kyorugi"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-slate-400 mb-1">Tanggal</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" value="16:00" required
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" value="18:00" required
                                class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1">Lokasi Latihan</label>
                        <input type="text" name="location" value="Dojang Eagle Pusat GOR Pajajaran" required
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>

                    <div>
                        <label class="block text-slate-400 mb-1">Keterangan / Deskripsi Latihan</label>
                        <textarea name="description" rows="2" placeholder="Fokus materi latihan hari ini..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-red-500"></textarea>
                    </div>

                    <!-- Pembagian Piket Asisten Pelatih (Integrated Duty Schedule) -->
                    <div class="p-3 rounded-2xl bg-slate-950 border border-amber-900/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">
                                Penugasan Petugas Piket Mengajar
                            </span>
                            <button type="button" @click="addCoachRow()" class="px-2 py-0.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold text-[10px]">
                                + Tambah Asisten
                            </button>
                        </div>

                        <template x-for="(row, index) in assignedRows" :key="index">
                            <div class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-slate-400 font-bold" x-text="'Petugas #' + (index + 1)"></span>
                                    <button type="button" @click="removeCoachRow(index)" class="text-rose-400 text-[10px] hover:underline" x-show="assignedRows.length > 1">Hapus</button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="coaches[]" required class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px]">
                                        <option value="">-- Pilih Asisten --</option>
                                        @foreach($coaches as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->belt_level }})</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="role_tasks[]" placeholder="Tugas (e.g. Instruktur Poomsae)" class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px]">
                                </div>
                                <input type="text" name="materials[]" placeholder="Materi (e.g. Taegeuk 1-3, Tendangan Dwi Chagi)" class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px]">
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" @click="showAddModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                            Simpan Jadwal & Piket
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
        assignedRows: [
            { coachId: '', roleTask: '', material: '' }
        ],

        addCoachRow() {
            this.assignedRows.push({ coachId: '', roleTask: '', material: '' });
        },

        removeCoachRow(idx) {
            this.assignedRows.splice(idx, 1);
        }
    }
}
</script>
@endpush
@endsection

