@extends('layouts.app')

@section('title', 'Cabang Dojang Taekwondo')

@section('content')
<div class="space-y-4" x-data="{ showAddModal: false }">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-red-500"></i> Cabang Tempat Latihan (Dojang)
            </h1>
            <p class="text-xs text-slate-400">Pengaturan unit cabang dan radius GPS absensi</p>
        </div>
        <button type="button" @click="showAddModal = true" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Cabang</span>
        </button>
    </div>

    <!-- Dojangs List -->
    <div class="space-y-3">
        @foreach($dojangs as $dojang)
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-white">{{ $dojang->name }}</h2>
                            <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold bg-red-950 text-red-400 border border-red-800/50">
                                {{ $dojang->code }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">{{ $dojang->address }}</p>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $dojang->is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-500' }}">
                        {{ $dojang->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <!-- GPS & Specs Badge -->
                <div class="grid grid-cols-3 gap-2 p-2.5 rounded-2xl bg-slate-950/70 border border-slate-800/80 text-[11px]">
                    <div>
                        <span class="text-slate-500 block text-[10px]">Radius GPS</span>
                        <strong class="text-white">{{ $dojang->radius_meters }} meter</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Asisten Pelatih</span>
                        <strong class="text-slate-300">{{ $dojang->coaches_count }} Orang</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px]">Sesi Latihan</span>
                        <strong class="text-slate-200">{{ $dojang->training_sessions_count }} Jadwal</strong>
                    </div>
                </div>

                @if($dojang->latitude && $dojang->longitude)
                    <div class="text-[10px] font-mono text-slate-500">
                        GPS: {{ $dojang->latitude }}, {{ $dojang->longitude }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Modal: Tambah Dojang Baru -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-md shadow-2xl space-y-4" @click.away="showAddModal = false">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-red-500"></i>
                Tambah Cabang Dojang Baru
            </h3>

            <form action="{{ route('dojangs.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">Nama Dojang</label>
                    <input type="text" name="name" required placeholder="Contoh: Dojang Eagle Unit Sukasari"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Kode Dojang</label>
                        <input type="text" name="code" required placeholder="DOJ-SUKA-04"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Radius GPS Toleransi (meter)</label>
                        <input type="number" name="radius_meters" value="150" required
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2" required placeholder="Jl. Raya Pajajaran No..."
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Latitude</label>
                        <input type="number" step="any" name="latitude" placeholder="-6.5828"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Longitude</label>
                        <input type="number" step="any" name="longitude" placeholder="106.7972"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showAddModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        Simpan Dojang
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

