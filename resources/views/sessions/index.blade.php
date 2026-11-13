@extends('layouts.app')

@section('title', 'Jadwal Sesi Latihan')

@section('content')
<div class="space-y-4" x-data="{ showAddModal: false }">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="clock" class="w-5 h-5 text-red-500"></i> Jadwal Sesi Latihan
            </h1>
            <p class="text-xs text-slate-400">Pengaturan jadwal mingguan sesi latihan taekwondo</p>
        </div>
        <button type="button" @click="showAddModal = true" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Jadwal</span>
        </button>
    </div>

    <!-- Sessions List -->
    <div class="space-y-2.5">
        @foreach($sessions as $ses)
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col items-center justify-center shrink-0">
                        <span class="text-[10px] font-bold text-red-500 uppercase">{{ substr($ses->day_of_week, 0, 3) }}</span>
                        <span class="text-xs font-mono font-bold text-white">{{ substr($ses->start_time, 0, 5) }}</span>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white">{{ $ses->name }}</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            {{ $ses->dojang->name }} • {{ $ses->day_of_week }} ({{ substr($ses->start_time, 0, 5) }} - {{ substr($ses->end_time, 0, 5) }} WIB)
                        </p>
                        @if($ses->session_pin)
                            <p class="text-[10px] text-amber-400 font-mono mt-0.5">
                                PIN Sesi: <strong>{{ $ses->session_pin }}</strong>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('sessions.destroy', $ses) }}" method="POST" onsubmit="return confirm('Hapus jadwal sesi latihan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 rounded-xl bg-slate-950 hover:bg-rose-950/60 border border-slate-800 hover:border-rose-700/60 flex items-center justify-center text-slate-500 hover:text-rose-400 transition-colors">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal: Tambah Jadwal Baru -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-md shadow-2xl space-y-4" @click.away="showAddModal = false">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-amber-400"></i>
                Tambah Jadwal Sesi Latihan
            </h3>

            <form action="{{ route('sessions.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">Pilih Cabang Dojang</label>
                    <select name="dojang_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        @foreach($dojangs as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Nama Sesi Latihan</label>
                    <input type="text" name="name" required placeholder="Contoh: Kelas Reguler Sabuk Putih-Kuning"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Hari</label>
                        <select name="day_of_week" required class="w-full px-2 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                <option value="{{ $hari }}">{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" value="16:00" required
                            class="w-full px-2 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Jam Selesai</label>
                        <input type="time" name="end_time" value="18:00" required
                            class="w-full px-2 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">PIN Sesi (Opsional)</label>
                    <input type="text" name="session_pin" maxlength="6" placeholder="Contoh: 1234"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showAddModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        Simpan Sesi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

