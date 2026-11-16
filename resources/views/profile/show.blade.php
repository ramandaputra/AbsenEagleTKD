@extends('layouts.app')

@section('title', 'Akun & Profil')

@section('content')
<div class="space-y-3.5 pb-4">

    {{-- 1. HERO PROFILE CARD --}}
    <div class="relative overflow-hidden bg-white rounded-2xl border border-[#DAE2FD] p-4 shadow-xs">
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
                <h2 class="text-base font-bold text-[#131B2E] truncate">{{ $user->name }}</h2>
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

    {{-- 2. UBAH PIN --}}
    <section x-data="{ showOldPin: false, showNewPin: false, showConfirmationPin: false }"
             class="bg-white rounded-2xl border border-[#DAE2FD] shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-xl">lock_reset</span>
                </div>
                <div class="min-w-0 pt-0.5">
                    <h3 class="text-sm font-bold text-[#131B2E]">Ubah PIN Presensi &amp; Keamanan</h3>
                    <p class="text-xs leading-relaxed text-[#64748B] mt-1">
                        Masukkan PIN lama Anda terlebih dahulu untuk verifikasi keamanan sebelum menetapkan 6 digit PIN baru:
                    </p>
                </div>
            </div>

            @if(session('success') && str_contains(session('success'), 'PIN'))
                <div class="mt-3 p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center space-x-2 text-emerald-700 text-xs font-semibold">
                    <span class="material-symbols-rounded text-base shrink-0">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @error('old_pin')
                <div class="mt-3 p-2.5 rounded-xl bg-red-50 border border-red-200 flex items-center space-x-2 text-red-700 text-[11.5px] font-semibold">
                    <span class="material-symbols-rounded text-base shrink-0">error</span>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <form action="{{ route('profile.pin') }}" method="POST" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="old_pin" class="block text-xs font-semibold text-[#131B2E] mb-1.5">PIN Lama</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-[#0051D5]" aria-hidden="true">lock_clock</span>
                        <input id="old_pin" name="old_pin" x-bind:type="showOldPin ? 'text' : 'password'"
                               inputmode="numeric" autocomplete="off"
                               maxlength="6" required
                               placeholder="PIN Lama (6 digit)"
                               class="w-full h-11 pl-11 pr-12 rounded-xl border border-[#DAE2FD] bg-[#F8FAFC] text-sm font-semibold tracking-[0.2em] text-[#131B2E] placeholder:tracking-normal placeholder:font-normal placeholder:text-[#94A3B8] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all @error('old_pin') border-rose-400 @enderror">
                        <button type="button" x-on:click="showOldPin = !showOldPin"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-[#F2F3FF] flex items-center justify-center">
                            <span class="material-symbols-rounded" x-text="showOldPin ? 'visibility' : 'visibility_off'">visibility_off</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="pin" class="block text-xs font-semibold text-[#131B2E] mb-1.5">PIN Baru (6 Digit)</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-[#0051D5]" aria-hidden="true">pin</span>
                        <input id="pin" name="pin" x-bind:type="showNewPin ? 'text' : 'password'"
                               inputmode="numeric" autocomplete="new-password"
                               maxlength="6" required placeholder="PIN Baru (6 digit)"
                               class="w-full h-11 pl-11 pr-12 rounded-xl border border-[#DAE2FD] bg-[#F8FAFC] text-sm font-semibold tracking-[0.2em] text-[#131B2E] placeholder:tracking-normal placeholder:font-normal placeholder:text-[#94A3B8] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all @error('pin') border-rose-400 @enderror">
                        <button type="button" x-on:click="showNewPin = !showNewPin"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-[#F2F3FF] flex items-center justify-center">
                            <span class="material-symbols-rounded" x-text="showNewPin ? 'visibility' : 'visibility_off'">visibility_off</span>
                        </button>
                    </div>
                    @error('pin')<p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="pin_confirmation" class="block text-xs font-semibold text-[#131B2E] mb-1.5">Konfirmasi PIN Baru</label>
                    <div class="relative">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-[#0051D5]" aria-hidden="true">verified_user</span>
                        <input id="pin_confirmation" name="pin_confirmation" x-bind:type="showConfirmationPin ? 'text' : 'password'"
                               inputmode="numeric" autocomplete="new-password"
                               maxlength="6" required placeholder="Konfirmasi PIN Baru (6 digit)"
                               class="w-full h-11 pl-11 pr-12 rounded-xl border border-[#DAE2FD] bg-[#F8FAFC] text-sm font-semibold tracking-[0.2em] text-[#131B2E] placeholder:tracking-normal placeholder:font-normal placeholder:text-[#94A3B8] focus:outline-none focus:border-[#0051D5] focus:bg-white transition-all @error('pin_confirmation') border-rose-400 @enderror">
                        <button type="button" x-on:click="showConfirmationPin = !showConfirmationPin"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-[#F2F3FF] flex items-center justify-center">
                            <span class="material-symbols-rounded" x-text="showConfirmationPin ? 'visibility' : 'visibility_off'">visibility_off</span>
                        </button>
                    </div>
                    @error('pin_confirmation')<p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex gap-2.5 pt-1">
                    <button type="reset" class="flex-1 h-11 rounded-xl border border-[#DAE2FD] text-[#64748B] hover:bg-[#F8FAFC] font-semibold text-sm transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 h-11 rounded-xl bg-[#0051D5] text-white hover:bg-[#003ea8] font-bold text-sm flex items-center justify-center gap-2 transition-colors active:scale-[0.99]">
                        Simpan PIN Baru
                    </button>
                </div>
            </form>
        </div>
    </section>
    {{-- 3. KELUAR DARI AKUN --}}
    <form action="{{ route('logout') }}" method="POST" class="pt-1">
        @csrf
        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')"
                class="w-full h-[46px] rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 font-bold text-[13.5px] flex items-center justify-center space-x-2 transition-colors active:scale-[0.99]">
            <span class="material-symbols-rounded text-lg">logout</span>
            <span>Keluar dari Akun</span>
        </button>
    </form>

    <p class="text-center text-[10.5px] text-[#64748B] pt-1 pb-3">
        Eagle Taekwondo Academy • Sistem Presensi Pelatih v2.0
    </p>

</div>
@endsection
