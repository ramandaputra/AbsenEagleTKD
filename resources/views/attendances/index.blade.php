@extends('layouts.app')

@section('title', 'Riwayat Absensi Pelatih')

@section('content')
<div class="space-y-4" x-data="{ showPhotoModal: false, modalPhotoUrl: '', showEditModal: false, editId: null, editStatus: 'Hadir', editNotes: '' }">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-5 h-5 text-red-500"></i> Riwayat Absensi
            </h1>
            <p class="text-xs text-slate-400">Catatan kehadiran tersimpan otomatis berdasarkan waktu server</p>
        </div>
        <a href="{{ route('attendances.create') }}" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md">
            <i data-lucide="camera" class="w-4 h-4"></i>
            <span>Check-in Baru</span>
        </a>
    </div>

    <!-- Filter Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
        <form method="GET" action="{{ route('attendances.index') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <!-- Start Date -->
            <div>
                <label class="block text-slate-400 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-slate-400 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-slate-400 mb-1">Status Kehadiran</label>
                <select name="status" class="w-full py-1.5 px-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    <option value="">Semua Status</option>
                    <option value="Hadir" {{ $status === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Izin" {{ $status === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ $status === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpa" {{ $status === 'Alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-1">
                <button type="submit" class="flex-1 py-1.5 px-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold transition-colors">
                    Filter
                </button>
                <a href="{{ route('attendances.index') }}" class="py-1.5 px-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Cards List -->
    @if($attendances->isEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center">
            <i data-lucide="calendar-x" class="w-12 h-12 text-slate-600 mx-auto mb-3"></i>
            <h3 class="text-sm font-bold text-white">Tidak ada data absensi</h3>
            <p class="text-xs text-slate-400 mt-1">Belum ada riwayat absensi yang cocok dengan filter ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($attendances as $att)
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex flex-col gap-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <!-- Photo Thumbnail with Modal Click -->
                            <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center shrink-0 cursor-pointer"
                                @click="modalPhotoUrl = '{{ $att->photos->first() ? asset($att->photos->first()->photo_path) : '' }}'; showPhotoModal = modalPhotoUrl !== ''">
                                @if($att->photos->isNotEmpty())
                                    <img src="/{{ $att->photos->first()->photo_path }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    <span class="text-base">
                                        {{ $att->status === 'Hadir' ? '🥋' : ($att->status === 'Izin' ? '📝' : ($att->status === 'Sakit' ? '🏥' : '❌')) }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-black text-white">
                                        {{ $att->date->translatedFormat('l, d F Y') }}
                                    </p>
                                    @if(auth()->user()->isAdmin())
                                        <span class="text-[10px] text-amber-400 font-semibold">• {{ $att->user->name }}</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $att->trainingSchedule ? $att->trainingSchedule->activity_name : 'Latihan Mandiri' }}
                                </p>
                                <p class="text-[10px] text-slate-500 font-mono">
                                    Tercatat: {{ substr($att->time, 0, 5) }} WIB
                                </p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="text-right shrink-0">
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold
                                {{ $att->status === 'Hadir' ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' :
                                  ($att->status === 'Izin' ? 'bg-amber-950 text-amber-400 border border-amber-800' :
                                   ($att->status === 'Sakit' ? 'bg-blue-950 text-blue-400 border border-blue-800' :
                                    'bg-rose-950 text-rose-400 border border-rose-800')) }}">
                                {{ $att->status }}
                            </span>
                            @if($att->status === 'Hadir')
                                <p class="text-xs font-mono font-bold text-emerald-400 mt-1">
                                    + Rp {{ number_format($att->session_rate, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($att->notes)
                        <div class="px-3 py-1.5 rounded-xl bg-slate-950/70 border border-slate-800/80 text-[11px] text-slate-300">
                            {{ $att->notes }}
                        </div>
                    @endif

                    <!-- Admin Correction Button -->
                    @if(auth()->user()->isAdmin())
                        <div class="pt-1.5 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                            <span class="text-slate-500">ID: #{{ $att->id }}</span>
                            <button type="button" @click="editId = {{ $att->id }}; editStatus = '{{ $att->status }}'; editNotes = '{{ addslashes($att->notes ?? '') }}'; showEditModal = true" class="text-amber-400 hover:text-amber-300 font-semibold flex items-center gap-1">
                                <i data-lucide="edit-2" class="w-3 h-3"></i> Koreksi Absensi
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-2">
                {{ $attendances->links() }}
            </div>
        </div>
    @endif

    <!-- Photo Modal -->
    <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md" @click="showPhotoModal = false">
        <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden p-3 shadow-2xl" @click.stop>
            <div class="flex items-center justify-between p-2">
                <span class="text-xs font-bold text-white">Bukti Foto Absensi</span>
                <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <img :src="modalPhotoUrl" alt="Bukti Foto" class="w-full h-auto rounded-2xl object-cover max-h-[70vh]">
        </div>
    </div>

    <!-- Admin Correction Modal -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" @click="showEditModal = false">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-sm shadow-2xl space-y-4" @click.stop>
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-amber-400"></i> Koreksi Data Absensi
            </h3>

            <form :action="'/attendances/' + editId" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-slate-400 mb-1">Status Kehadiran</label>
                    <select name="status" x-model="editStatus" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                        <option value="Hadir">Hadir</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Alpa">Alpa</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Keterangan / Alasan Koreksi</label>
                    <textarea name="notes" x-model="editNotes" rows="2" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showEditModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        Simpan Koreksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
