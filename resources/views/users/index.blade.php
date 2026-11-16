@extends('layouts.app')

@section('title', 'Kelola Pengguna & Role Pelatih')

@section('content')
<div class="space-y-3 pb-20" x-data="userManager()">

    <!-- Executive Header Banner -->
    <section class="relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] p-4 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-5 -top-5 h-36 w-36 rounded-full bg-blue-500/20 blur-xl"></div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-cyan-400/10 blur-xl"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full border border-blue-300/40 bg-blue-500/25 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                    Manajemen Pengguna
                </span>
                <h1 class="mt-1.5 text-sm font-extrabold tracking-tight">Kelola Pengguna &amp; Role</h1>
                <p class="mt-0.5 text-[11px] font-medium text-[#7DD3FC]">Tambah asisten pelatih dan atur hak akses role pengguna.</p>
            </div>

            <button type="button" @click="openAddModal()"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-xl bg-white px-3 text-[11px] font-bold text-[#00288E] shadow-sm transition-colors hover:bg-[#EAEDFF]">
                <span class="material-symbols-rounded text-base">person_add</span>
                <span>Tambah</span>
            </button>
        </div>
    </section>

    <!-- Filter Role Tabs Section -->
    <section class="rounded-xl border border-[#DAE2FD]/70 bg-white p-2 shadow-2xs">
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs">
            <a href="{{ route('users.index') }}"
               class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-colors {{ empty($roleFilter) ? 'bg-[#00288E] text-white shadow-xs' : 'bg-transparent text-[#64748B] hover:text-[#131B2E] hover:bg-[#F2F3FF]' }}">
                Semua ({{ $users->total() }})
            </a>
            <a href="{{ route('users.index', ['role' => 'asisten_pelatih']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'asisten_pelatih' ? 'bg-[#00288E] text-white shadow-xs' : 'bg-transparent text-[#64748B] hover:text-[#131B2E] hover:bg-[#F2F3FF]' }}">
                Asisten Pelatih
            </a>
            <a href="{{ route('users.index', ['role' => 'admin']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'admin' ? 'bg-[#00288E] text-white shadow-xs' : 'bg-transparent text-[#64748B] hover:text-[#131B2E] hover:bg-[#F2F3FF]' }}">
                Pelatih Utama
            </a>
            <a href="{{ route('users.index', ['role' => 'superadmin']) }}"
               class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition-colors {{ $roleFilter === 'superadmin' ? 'bg-[#00288E] text-white shadow-xs' : 'bg-transparent text-[#64748B] hover:text-[#131B2E] hover:bg-[#F2F3FF]' }}">
                Superadmin
            </a>
        </div>
    </section>

    <!-- Clean User List (Tampilan Hanya Nama dan Role) -->
    <section class="space-y-2">
        @forelse($users as $u)
            @php
                $roleName = $u->role?->name ?? 'asisten_pelatih';
                $roleBadgeClass = match($roleName) {
                    'superadmin' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'admin' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-blue-50 text-blue-700 border-blue-200',
                };
                $roleDisplayName = $u->role?->display_name ?? 'Asisten Pelatih';

                $userDetail = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?: '-',
                    'role_name' => $roleName,
                    'role_display' => $roleDisplayName,
                    'role_badge_class' => $roleBadgeClass,
                    'belt_level' => $u->belt_level ?: '-',
                    'dojang_id' => $u->dojang_id,
                    'dojang_name' => $u->dojang?->name ?? 'Semua Dojang (Pusat)',
                    'rate_per_session' => (int) $u->rate_per_session,
                    'bank_name' => $u->bank_name ?: '-',
                    'bank_account_number' => $u->bank_account_number ?: '-',
                    'bank_account_holder' => $u->bank_account_holder ?: '-',
                    'has_pin' => !empty($u->pin),
                    'is_active' => (bool) $u->is_active,
                    'avatar' => $u->avatar ? '/' . $u->avatar : null,
                    'is_self' => $u->id === auth()->id(),
                ];
            @endphp
            <article class="bg-white border border-[#DAE2FD]/80 hover:border-[#0051D5]/40 rounded-2xl p-3 shadow-2xs transition-all flex items-center justify-between gap-3">
                <!-- Sisi Kiri: Avatar, Nama, dan Role (Hanya Nama dan Role) -->
                <div class="flex items-center gap-3 min-w-0 flex-1 cursor-pointer"
                     @click="openDetailModal({{ json_encode($userDetail) }})"
                     title="Klik untuk melihat detail pengguna">
                    <div class="w-10 h-10 rounded-full {{ $roleName === 'superadmin' ? 'bg-[#00288E] text-white' : ($roleName === 'admin' ? 'bg-[#D97706] text-white' : 'bg-[#EAEDFF] text-[#0051D5]') }} flex items-center justify-center font-bold text-sm shrink-0 shadow-xs overflow-hidden">
                        @if($u->avatar)
                            <img src="/{{ $u->avatar }}" alt="{{ $u->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="material-symbols-rounded text-xl">
                                {{ $roleName === 'superadmin' ? 'security' : ($roleName === 'admin' ? 'workspace_premium' : 'sports_martial_arts') }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h2 class="text-[13px] font-bold text-[#131B2E] truncate">{{ $u->name }}</h2>
                            @if($u->id === auth()->id())
                                <span class="px-1.5 py-0.2 rounded-full bg-[#0051D5] text-white text-[9px] font-bold">Anda</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9.5px] font-bold border {{ $roleBadgeClass }}">
                                {{ $roleDisplayName }}
                            </span>
                            @if(!$u->is_active)
                                <span class="inline-block px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500">
                                    Non-aktif
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sisi Kanan: Tombol Edit, Hapus, dan Chevron Detail -->
                <div class="flex items-center gap-1 shrink-0">
                    <!-- Tombol Edit -->
                    <button type="button"
                            @click="openEditModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ $roleName }}', '{{ addslashes($u->phone ?? '') }}', '{{ addslashes($u->belt_level ?? '') }}', '{{ $u->dojang_id ?? '' }}', {{ $u->rate_per_session }}, '{{ addslashes($u->bank_name ?? '') }}', '{{ addslashes($u->bank_account_number ?? '') }}', '{{ addslashes($u->bank_account_holder ?? '') }}', {{ $u->is_active ? 1 : 0 }})"
                            title="Edit Pengguna"
                            aria-label="Edit pengguna {{ $u->name }}"
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#EAEDFF] text-[#0051D5] hover:bg-[#DAE2FD] transition-colors">
                        <span class="material-symbols-rounded text-base">edit</span>
                    </button>

                    <!-- Tombol Hapus -->
                    @if($u->id !== auth()->id())
                        <form action="{{ route('users.destroy', $u) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    title="Hapus Pengguna"
                                    aria-label="Hapus pengguna {{ $u->name }}"
                                    class="flex h-8 w-8 items-center justify-center rounded-xl text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition-colors">
                                <span class="material-symbols-rounded text-base">delete</span>
                            </button>
                        </form>
                    @endif

                    <!-- Tombol Chevron Right untuk Melihat Detail Pengguna -->
                    <button type="button"
                            @click="openDetailModal({{ json_encode($userDetail) }})"
                            title="Lihat Detail Pengguna"
                            aria-label="Lihat detail pengguna {{ $u->name }}"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-[#64748B] hover:bg-[#EAEDFF] hover:text-[#00288E] transition-colors">
                        <i class="bi bi-chevron-right text-sm" aria-hidden="true"></i>
                    </button>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">person_off</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Pengguna</p>
                <p class="text-[10.5px] text-[#64748B] mt-0.5">Klik tombol Tambah di atas untuk mendaftarkan asisten pelatih.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $users->links() }}
        </div>
    </section>

    <!-- Modal Detail Pengguna (Dibuka dari Tombol <i class="bi bi-chevron-right"></i>) -->
    <div x-show="showDetailModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="showDetailModal = false">

        <div class="bg-white border border-[#DAE2FD] rounded-3xl p-5 w-full max-w-md shadow-2xl space-y-4 my-8"
             @click.away="showDetailModal = false">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-2.5 border-b border-[#DAE2FD]">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-[#0051D5] text-xl">account_circle</span>
                    <h3 class="text-sm font-bold text-[#131B2E]">Detail Pengguna</h3>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-[#64748B] hover:text-[#131B2E] transition-colors">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>

            <!-- Profile Summary Card -->
            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-gradient-to-br from-[#F8FAFC] to-[#EEF2F6] border border-[#CBD5E1]/60">
                <div class="w-14 h-14 rounded-full flex items-center justify-center font-bold text-lg shrink-0 shadow-sm overflow-hidden"
                     :class="detailUser.role_name === 'superadmin' ? 'bg-[#00288E] text-white' : (detailUser.role_name === 'admin' ? 'bg-[#D97706] text-white' : 'bg-[#EAEDFF] text-[#0051D5]')">
                    <template x-if="detailUser.avatar">
                        <img :src="detailUser.avatar" :alt="detailUser.name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!detailUser.avatar">
                        <span class="material-symbols-rounded text-3xl">
                            <span x-text="detailUser.role_name === 'superadmin' ? 'security' : (detailUser.role_name === 'admin' ? 'workspace_premium' : 'sports_martial_arts')"></span>
                        </span>
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h4 class="text-sm font-bold text-[#131B2E] truncate" x-text="detailUser.name"></h4>
                        <template x-if="detailUser.is_self">
                            <span class="px-1.5 py-0.2 rounded-full bg-[#0051D5] text-white text-[9px] font-bold">Anda</span>
                        </template>
                    </div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border"
                              :class="detailUser.role_badge_class"
                              x-text="detailUser.role_display"></span>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                              :class="detailUser.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'"
                              x-text="detailUser.is_active ? 'Akun Aktif' : 'Non-aktif'"></span>
                    </div>
                </div>
            </div>

            <!-- Detail Information Sections -->
            <div class="space-y-2.5 text-xs">
                <!-- Kontak -->
                <div class="p-3 rounded-xl bg-white border border-[#DAE2FD]/80 space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">contact_mail</span>
                        <span>Informasi Kontak</span>
                    </div>
                    <div class="space-y-1.5 pt-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Email:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.email"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">No. HP / WA:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.phone"></span>
                        </div>
                    </div>
                </div>

                <!-- Keanggotaan & Dojang -->
                <div class="p-3 rounded-xl bg-white border border-[#DAE2FD]/80 space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">sports_martial_arts</span>
                        <span>Dojang &amp; Sabuk</span>
                    </div>
                    <div class="space-y-1.5 pt-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Tingkat Sabuk:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.belt_level"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Unit Dojang:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.dojang_name"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Status PIN Presensi:</span>
                            <span class="font-semibold" :class="detailUser.has_pin ? 'text-emerald-700' : 'text-amber-700'"
                                  x-text="detailUser.has_pin ? 'Sudah Diatur' : 'Belum Diatur'"></span>
                        </div>
                    </div>
                </div>

                <!-- Rekening Bank (Jika Ada) -->
                <div class="p-3 rounded-xl bg-white border border-[#DAE2FD]/80 space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#64748B] flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">account_balance</span>
                        <span>Rekening Bank</span>
                    </div>
                    <div class="space-y-1.5 pt-0.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Bank:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.bank_name"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">No. Rekening:</span>
                            <span class="font-mono font-semibold text-[#131B2E]" x-text="detailUser.bank_account_number"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B]">Atas Nama:</span>
                            <span class="font-semibold text-[#131B2E]" x-text="detailUser.bank_account_holder"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center gap-2 pt-2 border-t border-[#DAE2FD]">
                <button type="button" @click="showDetailModal = false"
                        class="flex-1 py-2.5 rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#64748B] hover:bg-[#F2F3FF] transition-colors">
                    Tutup
                </button>
                <button type="button" @click="editFromDetail()"
                        class="flex-1 py-2.5 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                    <span class="material-symbols-rounded text-base">edit</span>
                    <span>Edit Pengguna</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah / Ubah Role & Data Pengguna -->
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
                    <span class="material-symbols-rounded text-[#0051D5] text-xl">manage_accounts</span>
                    <h3 class="text-sm font-bold text-[#131B2E]"
                        x-text="isEdit ? 'Ubah Role &amp; Data Pengguna' : 'Tambah Asisten / Pengguna Baru'"></h3>
                </div>
                <button type="button" @click="showModal = false" class="text-[#64748B] hover:text-[#131B2E] transition-colors">
                    <span class="material-symbols-rounded text-lg">close</span>
                </button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Hidden Input Tarif (Diatur via Menu Rekap Gaji & Tarif) -->
                <input type="hidden" name="rate_per_session" :value="formData.rate_per_session">

                <!-- Pilihan Role -->
                <div>
                    <label class="block text-[11px] font-bold text-[#131B2E] mb-1">
                        Role Hak Akses <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" x-model="formData.role" required
                            class="w-full text-xs rounded-xl border border-[#0051D5] bg-[#F2F3FF] p-2.5 font-bold text-[#00288E] focus:outline-none focus:ring-2 focus:ring-[#0051D5]/20">
                        <option value="asisten_pelatih">Asisten Pelatih</option>
                        <option value="admin">Admin (Pelatih Utama)</option>
                        <option value="superadmin">Superadmin (Owner / Pimpinan)</option>
                    </select>
                    <p class="text-[10px] text-[#64748B] mt-0.5">Pilih role untuk mengatur hak akses akun.</p>
                </div>

                <!-- Nama & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required
                               placeholder="Nama pelatih"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" x-model="formData.email" required
                               placeholder="email@eagletkd.com"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                </div>

                <!-- Password & PIN -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">
                            <span x-text="isEdit ? 'Kata Sandi Baru (Opsional)' : 'Kata Sandi *'"></span>
                        </label>
                        <input type="password" name="password" :required="!isEdit"
                               placeholder="Minimal 4 karakter"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">
                            <span x-text="isEdit ? 'PIN Baru (Opsional)' : 'PIN (6 Digit)'"></span>
                        </label>
                        <input type="password" name="pin" maxlength="6"
                               placeholder="Contoh: 123456"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                </div>

                <!-- Sabuk & Dojang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Tingkat Sabuk</label>
                        <input type="text" name="belt_level" x-model="formData.belt_level"
                               placeholder="Contoh: Dan 2 Kukkiwon"
                               class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Cabang Dojang</label>
                        <select name="dojang_id" x-model="formData.dojang_id"
                                class="w-full text-xs rounded-xl border border-[#DAE2FD] bg-white p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                            <option value="">Semua Dojang (Pusat)</option>
                            @foreach($dojangs as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Nomor Telepon / WA -->
                <div>
                    <label class="block text-[10.5px] font-semibold text-[#131B2E] mb-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" x-model="formData.phone"
                           placeholder="08xxxxxxxxxx"
                           class="w-full text-xs rounded-xl border border-[#DAE2FD] p-2.5 text-[#131B2E] focus:border-[#0051D5] focus:outline-none">
                </div>

                <!-- Status Aktif (Saat Edit) -->
                <template x-if="isEdit">
                    <div class="pt-1 flex items-center gap-2">
                        <input type="checkbox" id="user_is_active" name="is_active" value="1"
                               :checked="formData.is_active"
                               class="w-4 h-4 rounded text-[#0051D5] focus:ring-[#0051D5]">
                        <label for="user_is_active" class="text-xs font-semibold text-[#131B2E]">Akun Aktif (Dapat Login &amp; Presensi)</label>
                    </div>
                </template>

                <div class="flex items-center gap-2 pt-2 border-t border-[#DAE2FD]">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#64748B] hover:bg-[#F2F3FF] transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white text-xs font-bold shadow-xs transition-colors">
                        <span x-text="isEdit ? 'Simpan Perubahan' : 'Daftarkan Pengguna'"></span>
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
        showDetailModal: false,
        isEdit: false,
        formAction: '{{ route('users.store') }}',
        detailUser: {},
        formData: {
            id: null,
            name: '',
            email: '',
            role: 'asisten_pelatih',
            phone: '',
            belt_level: '',
            dojang_id: '',
            rate_per_session: 50000,
            bank_name: '',
            bank_account_number: '',
            bank_account_holder: '',
            is_active: 1
        },

        openDetailModal(user) {
            this.detailUser = user;
            this.showDetailModal = true;
        },

        openAddModal() {
            this.isEdit = false;
            this.formAction = '{{ route('users.store') }}';
            this.formData = {
                id: null,
                name: '',
                email: '',
                role: 'asisten_pelatih',
                phone: '',
                belt_level: '',
                dojang_id: '',
                rate_per_session: 50000,
                bank_name: '',
                bank_account_number: '',
                bank_account_holder: '',
                is_active: 1
            };
            this.showModal = true;
        },

        openEditModal(id, name, email, role, phone, belt_level, dojang_id, rate_per_session, bank_name, bank_account_number, bank_account_holder, is_active) {
            this.isEdit = true;
            this.formAction = '/users/' + id;
            this.formData = {
                id: id,
                name: name,
                email: email,
                role: role,
                phone: phone || '',
                belt_level: belt_level || '',
                dojang_id: dojang_id ? String(dojang_id) : '',
                rate_per_session: rate_per_session || 50000,
                bank_name: bank_name || '',
                bank_account_number: bank_account_number || '',
                bank_account_holder: bank_account_holder || '',
                is_active: Boolean(is_active)
            };
            this.showDetailModal = false;
            this.showModal = true;
        },

        editFromDetail() {
            if (!this.detailUser.id) return;
            this.openEditModal(
                this.detailUser.id,
                this.detailUser.name,
                this.detailUser.email,
                this.detailUser.role_name,
                this.detailUser.phone === '-' ? '' : this.detailUser.phone,
                this.detailUser.belt_level === '-' ? '' : this.detailUser.belt_level,
                this.detailUser.dojang_id,
                this.detailUser.rate_per_session,
                this.detailUser.bank_name === '-' ? '' : this.detailUser.bank_name,
                this.detailUser.bank_account_number === '-' ? '' : this.detailUser.bank_account_number,
                this.detailUser.bank_account_holder === '-' ? '' : this.detailUser.bank_account_holder,
                this.detailUser.is_active ? 1 : 0
            );
        }
    }
}
</script>
@endpush
@endsection
