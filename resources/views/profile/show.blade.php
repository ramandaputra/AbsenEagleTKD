@extends('layouts.app')

@section('title', 'Akun & Profil')

@section('content')
<div class="space-y-3.5 pb-4">

    {{-- 1. HERO PROFILE CARD --}}
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
                {{-- Avatar upload hidden form --}}
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                    @csrf
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" class="hidden" onchange="document.getElementById('avatarForm').submit()">
                </form>
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

    {{-- 2. UBAH PIN 4 DIGIT --}}
    <div x-data="{ openPin: false }" class="bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs">
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

    {{-- 3. KELUAR DARI AKUN --}}
    <form action="{{ route('logout') }}" method="POST" class="pt-1">
        @csrf
        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')"
                class="w-full h-[46px] rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-[13.5px] flex items-center justify-center space-x-2 transition-colors active:scale-[0.99]">
            <span class="material-symbols-rounded text-lg">logout</span>
            <span>Keluar dari Akun</span>
        </button>
    </form>

    <!-- Discrete Brand Footer -->
    <p class="text-center text-[10.5px] text-[#64748B] pt-1 pb-3">
        Eagle Taekwondo Academy • Sistem Presensi Pelatih v2.0
    </p>

</div>
@endsection
