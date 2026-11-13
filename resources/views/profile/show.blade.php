@extends('layouts.app')

@section('title', 'Akun & Profil Pelatih')

@section('content')
<div class="space-y-3.5">

    {{-- 1. HERO PROFILE CARD (Matching Tirtafield _buildHeroProfileCard) --}}
    <div class="relative overflow-hidden bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs">
        <!-- Ambient decorative gradient circle -->
        <div class="absolute -right-6 -top-6 w-32 h-32 rounded-full bg-[#0051D5]/5 pointer-events-none"></div>

        <div class="relative z-10 flex items-start space-x-3.5">
            <!-- Avatar with Upload Camera Button Badge -->
            <div class="relative shrink-0">
                <div class="w-20 h-20 p-[2.5px] rounded-full bg-gradient-to-tr from-[#0051D5] to-[#7DD3FC] shadow-md shadow-[#0051D5]/20 flex items-center justify-center overflow-hidden">
                    @if($user->avatar)
                        <img src="/{{ $user->avatar }}" alt="Foto Profil" class="w-full h-full rounded-full object-cover">
                    @else
                        <div class="w-full h-full rounded-full bg-gradient-to-tr from-[#00288E] to-[#316BF3] text-white flex items-center justify-center text-2xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <label for="avatarInput"
                       class="absolute -bottom-1 -right-1 w-[30px] h-[30px] rounded-full bg-[#0051D5] text-white flex items-center justify-center border-2 border-white shadow-md hover:bg-[#003ea8] transition-colors cursor-pointer"
                       title="Ubah Foto Profil">
                    <span class="material-symbols-rounded text-sm">photo_camera</span>
                </label>
            </div>

            <!-- Identity Metadata -->
            <div class="flex-1 min-w-0 pt-1">
                <h2 class="text-base font-bold text-[#131B2E] truncate">
                    {{ $user->name }}
                </h2>
                <div class="text-xs text-[#64748B] mt-0.5 flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0051D5]/10 text-[#0051D5] border border-[#0051D5]/20">
                        {{ $user->role_display_name }}
                    </span>
                    @if($user->belt_level)
                        <span class="text-[11px] text-[#64748B] font-medium">{{ $user->belt_level }}</span>
                    @endif
                </div>
                @if($user->dojang)
                    <div class="text-xs text-[#64748B] truncate mt-1 flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm text-[#64748B]">apartment</span>
                        <span class="truncate">{{ $user->dojang->name }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. FORM SECTION: INFORMASI KEPELATIHAN & DATA PRIBADI --}}
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
        @csrf
        <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">

        <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-[#131B2E]">Informasi Kepelatihan</h3>
                <span class="text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200 font-semibold flex items-center gap-1">
                    <span class="material-symbols-rounded text-xs">verified</span> Data Terdaftar
                </span>
            </div>

            <!-- Field: Nama Lengkap -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Nama Lengkap</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">badge</span>
                    </span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Field: Email -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Alamat Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">mail</span>
                    </span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Field: No HP / WhatsApp -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Nomor WhatsApp / HP</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">call</span>
                    </span>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0812-xxxx-xxxx"
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Field: Tingkatan Sabuk -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Tingkatan Sabuk (Dan / Geup)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">sports_martial_arts</span>
                    </span>
                    <input type="text" name="belt_level" value="{{ old('belt_level', $user->belt_level) }}" placeholder="Dan 2 Kukkiwon"
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Field: Dojang Penempatan (Pilihan dari tabel database dojangs) -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Dojang Penempatan</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">apartment</span>
                    </span>
                    <select name="dojang_id"
                            class="w-full pl-10 pr-8 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                        <option value="">-- Pilih Cabang Dojang --</option>
                        @foreach($dojangs ?? [] as $dj)
                            <option value="{{ $dj->id }}" {{ old('dojang_id', $user->dojang_id) == $dj->id ? 'selected' : '' }}>
                                {{ $dj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- 3. INFORMASI REKENING TRANSFER HONOR --}}
        <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <span class="material-symbols-rounded text-base">credit_card</span>
                    </div>
                    <h3 class="text-[15px] font-bold text-[#131B2E]">Rekening Transfer Honor</h3>
                </div>
                <span class="text-[10.5px] text-[#64748B]">Transfer Manual Klub</span>
            </div>

            <!-- Nama Bank -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Nama Bank</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">account_balance</span>
                    </span>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $user->bank_name) }}" placeholder="Contoh: BCA / Mandiri / BRI"
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Nomor Rekening -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Nomor Rekening</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">tag</span>
                    </span>
                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" placeholder="1234567890"
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-mono font-bold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>

            <!-- Atas Nama Pemilik -->
            <div>
                <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">Atas Nama (Pemilik Rekening)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <span class="material-symbols-rounded text-xl">person</span>
                    </span>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $user->bank_account_holder) }}" placeholder="Nama sesuai buku rekening"
                           class="w-full pl-10 pr-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                </div>
            </div>
        </div>

        <!-- Tombol Simpan Perubahan Profil -->
        <button type="submit"
                class="w-full h-12 rounded-xl bg-[#316BF3] hover:bg-[#2558d4] text-white font-bold text-[14.5px] flex items-center justify-center space-x-2 shadow-sm transition-all active:scale-[0.99]">
            <span class="material-symbols-rounded text-xl">save</span>
            <span>Simpan Perubahan Profil</span>
        </button>
    </form>

    {{-- 4. KEAMANAN: UBAH PIN 4 DIGIT & KATA SANDI (Tirtafield Clean Security Section) --}}
    <div x-data="{ openPin: false, openPassword: false }" class="space-y-3">

        <!-- Card: Ubah PIN 4 Digit Presensi -->
        <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs">
            <div @click="openPin = !openPin" class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                        <span class="material-symbols-rounded text-xl">pin</span>
                    </div>
                    <div>
                        <h4 class="text-[13.5px] font-bold text-[#131B2E]">Ubah PIN Presensi (4 Digit)</h4>
                        <p class="text-[11px] text-[#64748B]">PIN saat ini: •••• Digunakan untuk login cepat</p>
                    </div>
                </div>
                <span class="material-symbols-rounded text-xl text-[#64748B] transition-transform" :class="openPin ? 'rotate-180' : ''">expand_more</span>
            </div>

            <!-- Collapsible Form: Ubah PIN -->
            <div x-show="openPin" x-cloak class="mt-3 pt-3 border-t border-[#EAEDFF]">
                <form action="{{ route('profile.pin') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#131B2E] mb-1.5">PIN 4 Digit Baru</label>
                        <input type="password" name="pin" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" required placeholder="Contoh: 1234"
                               class="w-full px-4 py-2.5 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-center font-mono text-lg font-bold text-[#131B2E] tracking-widest focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all">
                        <span class="text-[10.5px] text-[#64748B] mt-1 block text-center">Masukkan tepat 4 angka numerik.</span>
                    </div>

                    <button type="submit"
                            class="w-full h-10 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white font-bold text-xs flex items-center justify-center space-x-1.5 transition-colors">
                        <span class="material-symbols-rounded text-base">check</span>
                        <span>Perbarui PIN 4 Digit</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Card: Ubah Kata Sandi -->
        <div class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs">
            <div @click="openPassword = !openPassword" class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                        <span class="material-symbols-rounded text-xl">key</span>
                    </div>
                    <div>
                        <h4 class="text-[13.5px] font-bold text-[#131B2E]">Ubah Kata Sandi Akun</h4>
                        <p class="text-[11px] text-[#64748B]">Perbarui kata sandi untuk keamanan akun</p>
                    </div>
                </div>
                <span class="material-symbols-rounded text-xl text-[#64748B] transition-transform" :class="openPassword ? 'rotate-180' : ''">expand_more</span>
            </div>

            <!-- Collapsible Form: Ubah Sandi -->
            <div x-show="openPassword" x-cloak class="mt-3 pt-3 border-t border-[#EAEDFF]">
                <form action="{{ route('profile.password') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#131B2E] mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="••••••••"
                               class="w-full px-3 py-2 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[#131B2E] mb-1">Kata Sandi Baru</label>
                            <input type="password" name="password" required placeholder="Min 6 karakter"
                                   class="w-full px-3 py-2 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#131B2E] mb-1">Ulangi Kata Sandi</label>
                            <input type="password" name="password_confirmation" required placeholder="Konfirmasi"
                                   class="w-full px-3 py-2 bg-[#FAF8FF] rounded-xl border border-[#DAE2FD] text-xs font-semibold text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full h-10 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white font-bold text-xs flex items-center justify-center space-x-1.5 transition-colors">
                        <span class="material-symbols-rounded text-base">check</span>
                        <span>Perbarui Kata Sandi</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- 5. ACTION BUTTON: KELUAR DARI AKUN (Matching Tirtafield exactly) --}}
    <form action="{{ route('logout') }}" method="POST" class="pt-2">
        @csrf
        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')"
                class="w-full h-[46px] rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-[13.5px] flex items-center justify-center space-x-2 transition-colors active:scale-[0.99]">
            <span class="material-symbols-rounded text-lg">logout</span>
            <span>Keluar dari Akun</span>
        </button>
    </form>

    <!-- Discrete Brand Footer -->
    <p class="text-center text-[10.5px] text-[#64748B] pt-2 pb-3">
        Eagle Taekwondo Academy • Sistem Presensi Pelatih v2.0
    </p>

</div>
@endsection
