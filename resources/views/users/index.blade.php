@extends('layouts.app')

@section('title', 'Data Pelatih & Tarif Honor')

@section('content')
<div class="space-y-4" x-data="userManager()">

    <!-- Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-red-500"></i> Data Pelatih & Tarif Honor
            </h1>
            <p class="text-xs text-slate-400">Pengaturan 3 role pengguna dan tarif honor per kehadiran sesi</p>
        </div>
        <button type="button" @click="openAddModal()" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-md">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Pelatih</span>
        </button>
    </div>

    <!-- Filter Role -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar text-xs pb-1">
        <a href="{{ route('users.index') }}" class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-colors {{ empty($roleFilter) ? 'bg-red-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Semua ({{ $users->total() }})
        </a>
        <a href="{{ route('users.index', ['role' => 'asisten_pelatih']) }}" class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'asisten_pelatih' ? 'bg-red-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Asisten Pelatih
        </a>
        <a href="{{ route('users.index', ['role' => 'admin']) }}" class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'admin' ? 'bg-red-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Pelatih Utama
        </a>
        <a href="{{ route('users.index', ['role' => 'superadmin']) }}" class="px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'superadmin' ? 'bg-red-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Superadmin
        </a>
    </div>

    <!-- Users List -->
    <div class="space-y-3">
        @foreach($users as $u)
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg flex flex-col gap-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden flex items-center justify-center font-bold text-white text-lg shrink-0">
                            @if($u->profile_photo)
                                <img src="/{{ $u->profile_photo }}" alt="Foto" class="w-full h-full object-cover">
                            @else
                                {{ $u->role->value === 'superadmin' ? '👑' : ($u->role->value === 'admin' ? '🥋' : '🏅') }}
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-sm font-bold text-white">{{ $u->name }}</h2>
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold {{ $u->role->badgeClass() }}">
                                    {{ $u->role->label() }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ $u->email }} • {{ $u->phone ?: 'No HP -' }}
                            </p>
                            <p class="text-[10px] text-slate-500 mt-0.5">
                                Sabuk: <strong class="text-slate-300">{{ $u->belt_level ?: '-' }}</strong> | Dojang: <strong class="text-slate-300">{{ $u->dojang ? $u->dojang->name : 'Semua Unit' }}</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Tarif Honor Display -->
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 font-semibold block">Tarif per Kehadiran</span>
                        <div class="text-sm font-mono font-black text-emerald-400">
                            Rp {{ number_format($u->rate_per_session, 0, ',', '.') }}
                        </div>
                        <span class="text-[9px] text-slate-500">/ kehadiran sesi</span>
                    </div>
                </div>

                <!-- Rekening Info & Edit Button -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-800 text-xs">
                    <div class="text-[11px] text-slate-400 font-mono">
                        Bank: <strong class="text-slate-300">{{ $u->bank_name ?: '-' }} {{ $u->bank_account_number ?: '-' }}</strong>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ $u->role->value }}', '{{ addslashes($u->phone ?? '') }}', '{{ addslashes($u->belt_level ?? '') }}', '{{ $u->dojang_id }}', {{ $u->rate_per_session }}, '{{ addslashes($u->bank_name ?? '') }}', '{{ addslashes($u->bank_account_number ?? '') }}', '{{ addslashes($u->bank_account_holder ?? '') }}', {{ $u->is_active ? 1 : 0 }})"
                            class="px-2.5 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 font-semibold flex items-center gap-1 transition-colors">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Edit & Tarif</span>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Form: Tambah / Edit Pelatih -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 w-full max-w-md shadow-2xl space-y-4 my-8" @click.away="showModal = false">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <i data-lucide="user-cog" class="w-4 h-4 text-red-500"></i>
                <span x-text="isEdit ? 'Ubah Data Pelatih & Tarif Honor' : 'Daftarkan Pelatih Baru'"></span>
            </h3>

            <form :action="formAction" method="POST" class="space-y-3 text-xs">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-slate-400 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="form.name" required
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Email Akun</label>
                        <input type="email" name="email" x-model="form.email" required
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Role Akun</label>
                        <select name="role" x-model="form.role" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                            <option value="asisten_pelatih">Asisten Pelatih</option>
                            <option value="admin">Pelatih Utama (Admin)</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Kata Sandi (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" :required="!isEdit" placeholder="••••••••"
                        class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                </div>

                <!-- Tarif Honor per Sesi (FITUR UTAMA) -->
                <div class="p-3 rounded-2xl bg-slate-950 border border-red-900/40">
                    <label class="block text-xs font-bold text-emerald-400 mb-1">
                        Tarif Honor per Kehadiran Sesi (Rp)
                    </label>
                    <input type="number" step="5000" name="rate_per_session" x-model="form.rate_per_session" required
                        class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-emerald-700 text-white font-mono font-bold text-sm focus:outline-none focus:border-emerald-500">
                    <p class="text-[10px] text-slate-500 mt-1">
                        Honor yang otomatis dikalikan dengan jumlah kehadiran asisten pelatih dalam 1 bulan.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Tingkatan Sabuk</label>
                        <input type="text" name="belt_level" x-model="form.belt_level" placeholder="Dan 1 Kukkiwon"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Cabang Dojang Utama</label>
                        <select name="dojang_id" x-model="form.dojang_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                            <option value="">-- Semua Cabang --</option>
                            @foreach($dojangs as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Rekening Bank -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-400 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" x-model="form.bank_name" placeholder="BCA / Mandiri / BRI"
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Nomor Rekening</label>
                        <input type="text" name="bank_account_number" x-model="form.bank_account_number" placeholder="882019..."
                            class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold">
                        Simpan Pelatih
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function userManager() {
    return {
        showModal: false,
        isEdit: false,
        formAction: '{{ route('users.store') }}',
        form: {
            name: '',
            email: '',
            role: 'asisten_pelatih',
            phone: '',
            belt_level: '',
            dojang_id: '',
            rate_per_session: 50000,
            bank_name: 'BCA',
            bank_account_number: '',
            bank_account_holder: ''
        },

        openAddModal() {
            this.isEdit = false;
            this.formAction = '{{ route('users.store') }}';
            this.form = {
                name: '',
                email: '',
                role: 'asisten_pelatih',
                phone: '',
                belt_level: 'Dan 1 Kukkiwon',
                dojang_id: '',
                rate_per_session: 50000,
                bank_name: 'BCA',
                bank_account_number: '',
                bank_account_holder: ''
            };
            this.showModal = true;
        },

        openEditModal(id, name, email, role, phone, belt_level, dojang_id, rate, bank_name, bank_number, bank_holder, is_active) {
            this.isEdit = true;
            this.formAction = '/coaches/' + id;
            this.form = {
                name: name,
                email: email,
                role: role,
                phone: phone,
                belt_level: belt_level,
                dojang_id: dojang_id || '',
                rate_per_session: rate,
                bank_name: bank_name,
                bank_account_number: bank_number,
                bank_account_holder: bank_holder
            };
            this.showModal = true;
        }
    }
}
</script>
@endpush
@endsection

