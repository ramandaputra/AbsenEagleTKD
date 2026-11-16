@extends('layouts.app')

@section('title', 'Cabang Dojang Taekwondo')

@section('content')
<div class="space-y-3.5 pb-20" x-data="dojangManager()">

    <!-- Top Navigation & Action Card -->
    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('dashboard') }}"
               class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#131B2E] hover:bg-[#DAE2FD] flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-[#131B2E] truncate">Cabang Unit Dojang</h1>
                <p class="text-[11px] text-[#64748B] truncate mt-0.5">Daftar lokasi tempat latihan &amp; radius presensi</p>
            </div>
        </div>

        <button type="button" @click="openAddModal()"
                class="px-3 py-2 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white font-bold text-xs flex items-center gap-1.5 shadow-xs transition-colors shrink-0">
            <span class="material-symbols-rounded text-base">add</span>
            <span>Tambah</span>
        </button>
    </div>

    <!-- Dojangs List -->
    <div class="space-y-2.5">
        @forelse($dojangs as $dojang)
            <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3.5 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-2.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                            <span class="material-symbols-rounded text-2xl">domain</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h2 class="text-xs sm:text-sm font-bold text-[#131B2E] truncate">{{ $dojang->name }}</h2>
                                <span class="text-[9.5px] px-2 py-0.5 rounded-full font-mono font-bold bg-[#FAF8FF] text-[#0051D5] border border-[#DAE2FD]">
                                    {{ $dojang->code }}
                                </span>
                            </div>
                            <p class="text-[10.5px] text-[#64748B] mt-0.5 line-clamp-2">
                                {{ $dojang->address }}
                            </p>
                        </div>
                    </div>

                    <span class="text-[9.5px] px-2 py-0.5 rounded-full font-bold shrink-0 border {{ $dojang->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                        {{ $dojang->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <!-- Specs Grid Box -->
                <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-[#FAF8FF] border border-[#EAEDFF] text-[10.5px]">
                    <div>
                        <span class="text-[#64748B] block text-[9.5px]">Radius GPS</span>
                        <strong class="text-[#131B2E] font-bold">{{ $dojang->radius_meters }} meter</strong>
                    </div>
                    <div>
                        <span class="text-[#64748B] block text-[9.5px]">Asisten Pelatih</span>
                        <strong class="text-[#0051D5] font-bold">{{ $dojang->coaches_count }} Orang</strong>
                    </div>
                    <div>
                        <span class="text-[#64748B] block text-[9.5px]">Jadwal Sesi</span>
                        <strong class="text-[#131B2E] font-bold">{{ $dojang->training_sessions_count }} Sesi</strong>
                    </div>
                </div>

                @if($dojang->latitude && $dojang->longitude)
                    <div class="flex items-center gap-1 text-[10px] font-mono text-[#64748B]">
                        <span class="material-symbols-rounded text-xs text-[#0051D5]">location_on</span>
                        <span>{{ $dojang->latitude }}, {{ $dojang->longitude }}</span>
                    </div>
                @endif

                <!-- Actions: Edit & Hapus -->
                <div class="flex items-center justify-end gap-1.5 pt-1 border-t border-[#DAE2FD]/60 text-xs">
                    <button type="button"
                            @click="openEditModal({{ $dojang->id }}, '{{ addslashes($dojang->name) }}', '{{ $dojang->code }}', '{{ addslashes($dojang->address) }}', '{{ $dojang->latitude ?? '' }}', '{{ $dojang->longitude ?? '' }}', {{ $dojang->radius_meters }}, '{{ addslashes($dojang->phone ?? '') }}', {{ $dojang->is_active ? 1 : 0 }})"
                            class="px-2.5 py-1 rounded-xl bg-[#EAEDFF] hover:bg-[#DAE2FD] text-[#0051D5] font-bold text-[11px] flex items-center gap-1 transition-colors">
                        <span class="material-symbols-rounded text-sm">edit</span>
                        <span>Ubah Data</span>
                    </button>

                    <form action="{{ route('dojangs.destroy', $dojang) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang {{ $dojang->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Hapus dojang"
                                class="p-1 rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors flex items-center justify-center">
                            <span class="material-symbols-rounded text-base">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">domain_disabled</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Cabang Dojang</p>
                <p class="text-[10.5px] text-[#64748B] mt-0.5">Klik tombol Tambah di atas untuk mendaftarkan unit dojang baru.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Form: Tambah / Edit Dojang -->
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="showModal = false">

        <div class="bg-white border border-[#DAE2FD] rounded-3xl p-5 w-full max-w-md shadow-2xl space-y-4 my-8"
             @click.away="showModal = false">

            <div class="flex items-center justify-between pb-2 border-b border-[#DAE2FD]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-[#0051D5] text-xl">domain</span>
                    <h3 class="text-sm font-bold text-[#131B2E]"
                        x-text="isEdit ? 'Ubah Cabang Dojang' : 'Tambah Cabang Dojang Baru'"></h3>
                </div>
                <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#131B2E]">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Nama Dojang <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="formData.name" required
                           placeholder="Contoh: Dojang Unit Tanjungpinang"
                           class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Kode Cabang <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="formData.code" required
                               placeholder="DOJ-TPI-01"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 font-mono text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Radius Toleransi (m) <span class="text-rose-500">*</span></label>
                        <input type="number" name="radius_meters" x-model="formData.radius_meters" required min="10" max="2000"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 font-mono text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Alamat Lengkap <span class="text-rose-500">*</span></label>
                    <textarea name="address" x-model="formData.address" rows="2" required
                              placeholder="Jl. Hang Tuah No..."
                              class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Latitude (GPS)</label>
                        <input type="number" step="any" name="latitude" x-model="formData.latitude"
                               placeholder="0.9123"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 font-mono text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Longitude (GPS)</label>
                        <input type="number" step="any" name="longitude" x-model="formData.longitude"
                               placeholder="104.4567"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 font-mono text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                </div>

                <template x-if="isEdit">
                    <div class="pt-1 flex items-center gap-2">
                        <input type="checkbox" id="dojang_is_active" name="is_active" value="1"
                               :checked="formData.is_active"
                               class="w-4 h-4 rounded text-[#0051D5] focus:ring-[#0051D5]">
                        <label for="dojang_is_active" class="text-xs font-semibold text-[#131B2E]">Cabang Aktif Digunakan</label>
                    </div>
                </template>

                <div class="flex items-center gap-2 pt-2 border-t border-[#DAE2FD]">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#64748B] hover:bg-[#F2F3FF] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white text-xs font-bold shadow-xs transition-colors">
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah Cabang'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function dojangManager() {
    return {
        showModal: false,
        isEdit: false,
        formAction: '{{ route('dojangs.store') }}',
        formData: {
            name: '',
            code: '',
            address: '',
            latitude: '',
            longitude: '',
            radius_meters: 150,
            phone: '',
            is_active: 1
        },

        openAddModal() {
            this.isEdit = false;
            this.formAction = '{{ route('dojangs.store') }}';
            this.formData = {
                name: '',
                code: '',
                address: '',
                latitude: '',
                longitude: '',
                radius_meters: 150,
                phone: '',
                is_active: 1
            };
            this.showModal = true;
        },

        openEditModal(id, name, code, address, latitude, longitude, radius_meters, phone, is_active) {
            this.isEdit = true;
            this.formAction = '/dojangs/' + id;
            this.formData = {
                name: name,
                code: code,
                address: address,
                latitude: latitude,
                longitude: longitude,
                radius_meters: radius_meters,
                phone: phone,
                is_active: Boolean(is_active)
            };
            this.showModal = true;
        }
    }
}
</script>
@endpush
@endsection
