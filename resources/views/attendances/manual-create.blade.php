@extends('layouts.app')

@section('title', 'Input Absensi Manual')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-red-500"></i> Input Absensi Manual
            </h1>
            <p class="text-xs text-slate-400">Catat kehadiran asisten pelatih secara manual oleh Pelatih Utama</p>
        </div>
        <a href="{{ route('attendances.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs">
            Batal
        </a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-lg">
        <form action="{{ route('attendances.manual.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Pilih Asisten Pelatih -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Pilih Asisten Pelatih
                </label>
                <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                    <option value="">-- Pilih Asisten Pelatih --</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}">
                            {{ $coach->name }} ({{ $coach->belt_level }}) - Rp {{ number_format($coach->rate_per_session, 0, ',', '.') }}/sesi
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Pilih Dojang -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Cabang Dojang
                </label>
                <select name="dojang_id" required class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                    @foreach($dojangs as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pilih Sesi Latihan -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Sesi Latihan
                </label>
                <select name="training_session_id" class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                    <option value="">-- Sesi Bebas / Tambahan --</option>
                    @foreach($sessions as $ses)
                        <option value="{{ $ses->id }}">
                            {{ $ses->name }} ({{ $ses->day_of_week }}, {{ substr($ses->start_time, 0, 5) }} - {{ substr($ses->end_time, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tanggal & Jam -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Tanggal Latihan</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Jam Masuk</label>
                    <input type="time" name="check_in_time" value="16:00" required
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">Jam Pulang (Opsional)</label>
                    <input type="time" name="check_out_time" value="18:00"
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:border-red-500">
                </div>
            </div>

            <!-- Catatan Materi -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                    Catatan Materi Melatih
                </label>
                <textarea name="notes" rows="2" placeholder="Materi yang dilatih / alasan input manual..."
                    class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-red-500"></textarea>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 rounded-2xl font-bold text-sm bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500 text-white shadow-lg shadow-red-950/80 transition-all flex items-center justify-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Simpan & Verifikasi Kehadiran</span>
            </button>
        </form>
    </div>
</div>
@endsection

