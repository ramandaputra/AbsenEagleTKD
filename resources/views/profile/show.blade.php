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

    {{-- 2. UBAH PIN — Tirtafield Numeric Keypad Style --}}
    <div x-data="pinChangeApp()" class="bg-white rounded-2xl border border-[#DAE2FD] shadow-xs overflow-hidden">

        <!-- Section Header (Collapsible trigger) -->
        <div @click="open = !open" class="flex items-center justify-between p-4 cursor-pointer select-none">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-[#EAEDFF] text-[#0051D5] flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-xl">pin</span>
                </div>
                <div>
                    <h4 class="text-[13.5px] font-bold text-[#131B2E]">Ubah PIN Presensi (4 Digit)</h4>
                    <p class="text-[11px] text-[#64748B]">PIN digunakan untuk login cepat ke aplikasi</p>
                </div>
            </div>
            <span class="material-symbols-rounded text-xl text-[#64748B] transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
        </div>

        <!-- Collapsible PIN Keypad Panel -->
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-[#EAEDFF] px-4 pt-5 pb-6">

            <!-- Step indicator -->
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="h-1 w-10 rounded-full transition-colors duration-300" :class="step === 'new' ? 'bg-[#0051D5]' : 'bg-[#DAE2FD]'"></div>
                <div class="h-1 w-10 rounded-full transition-colors duration-300" :class="step === 'confirm' ? 'bg-[#0051D5]' : 'bg-[#DAE2FD]'"></div>
            </div>

            <!-- Label -->
            <div class="text-center mb-3">
                <p class="text-[13px] font-bold text-[#131B2E]" x-text="step === 'new' ? 'Masukkan PIN Baru' : 'Konfirmasi PIN Baru'"></p>
                <p class="text-[11px] text-[#64748B] mt-0.5" x-text="step === 'new' ? 'Ketuk angka untuk memasukkan 4 digit PIN' : 'Ketuk ulang PIN yang sama untuk konfirmasi'"></p>
            </div>

            <!-- 4 Dot Indicators (Tirtafield style) -->
            <div class="flex items-center justify-center space-x-4 my-4" :class="{ 'animate-shake': hasShake }">
                <template x-for="i in 4" :key="i">
                    <div class="w-4 h-4 rounded-full border-2 transition-all duration-150"
                         :class="isError
                            ? 'bg-rose-500 border-rose-500'
                            : (i <= currentPin.length
                                ? 'bg-[#00288E] border-[#00288E] shadow-[0_0_8px_rgba(0,40,142,0.35)] scale-110'
                                : 'bg-transparent border-[#C4C5D5]')">
                    </div>
                </template>
            </div>

            <!-- Status / Error message -->
            <div class="h-5 flex items-center justify-center mb-5">
                <template x-if="isError">
                    <span class="text-xs font-bold text-rose-600" x-text="errorMsg"></span>
                </template>
                <template x-if="isSuccess">
                    <span class="text-xs font-bold text-emerald-600">✓ PIN berhasil diperbarui!</span>
                </template>
                <template x-if="!isError && !isSuccess && currentPin.length === 0">
                    <span class="text-[11px] text-[#64748B]">Ketuk angka di bawah</span>
                </template>
            </div>

            <!-- Hidden POST form -->
            <form id="pinForm" action="{{ route('profile.pin') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="pin" x-bind:value="confirmedPin">
            </form>

            <!-- Numeric Keypad 3×4 (Tirtafield Exact Spec) -->
            <div class="grid grid-cols-3 gap-y-3 gap-x-4 justify-items-center max-w-[260px] mx-auto">
                <!-- Row 1 -->
                <button type="button" @click="pressDigit('1')" class="pin-key">1</button>
                <button type="button" @click="pressDigit('2')" class="pin-key">2</button>
                <button type="button" @click="pressDigit('3')" class="pin-key">3</button>
                <!-- Row 2 -->
                <button type="button" @click="pressDigit('4')" class="pin-key">4</button>
                <button type="button" @click="pressDigit('5')" class="pin-key">5</button>
                <button type="button" @click="pressDigit('6')" class="pin-key">6</button>
                <!-- Row 3 -->
                <button type="button" @click="pressDigit('7')" class="pin-key">7</button>
                <button type="button" @click="pressDigit('8')" class="pin-key">8</button>
                <button type="button" @click="pressDigit('9')" class="pin-key">9</button>
                <!-- Row 4 -->
                <div class="w-[64px] h-[64px]"></div>
                <button type="button" @click="pressDigit('0')" class="pin-key">0</button>
                <button type="button" @click="pressBackspace()" class="pin-key flex items-center justify-center" title="Hapus">
                    <span class="material-symbols-rounded text-2xl">backspace</span>
                </button>
            </div>

            <!-- Reset / Cancel -->
            <div class="mt-5 text-center">
                <button type="button" @click="resetAll()"
                        class="text-[11.5px] font-semibold text-[#64748B] hover:text-[#131B2E] transition-colors">
                    Batalkan Perubahan PIN
                </button>
            </div>
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

    <p class="text-center text-[10.5px] text-[#64748B] pt-1 pb-3">
        Eagle Taekwondo Academy • Sistem Presensi Pelatih v2.0
    </p>

</div>

<style>
    .pin-key {
        width: 64px;
        height: 64px;
        border-radius: 9999px;
        background-color: #ffffff;
        border: 1.5px solid #DAE2FD;
        color: #131B2E;
        font-size: 24px;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: all 0.12s ease-in-out;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
        cursor: pointer;
    }
    .pin-key:active {
        transform: scale(0.91);
        background-color: #EAEDFF;
        border-color: #316BF3;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-7px); }
        40%, 80% { transform: translateX(7px); }
    }
    .animate-shake {
        animation: shake 0.35s ease-in-out;
    }
    [x-cloak] { display: none !important; }
</style>

@endsection

@section('scripts')
<script>
    function pinChangeApp() {
        return {
            open: false,
            step: 'new',       // 'new' | 'confirm'
            currentPin: '',
            newPin: '',
            confirmedPin: '',
            isError: false,
            isSuccess: false,
            hasShake: false,
            errorMsg: '',

            pressDigit(d) {
                if (this.isSuccess) return;
                if (this.currentPin.length >= 4) return;

                this.isError = false;
                this.currentPin += d;

                if (this.currentPin.length === 4) {
                    this.$nextTick(() => setTimeout(() => this.onPinComplete(), 220));
                }
            },

            pressBackspace() {
                if (this.isSuccess) return;
                if (this.currentPin.length > 0) {
                    this.isError = false;
                    this.currentPin = this.currentPin.slice(0, -1);
                }
            },

            onPinComplete() {
                if (this.step === 'new') {
                    this.newPin = this.currentPin;
                    this.currentPin = '';
                    this.step = 'confirm';
                } else {
                    // Confirm step
                    if (this.currentPin === this.newPin) {
                        this.confirmedPin = this.currentPin;
                        this.currentPin = '';
                        this.submitPin();
                    } else {
                        this.triggerError('PIN tidak cocok. Coba lagi dari awal.');
                        this.step = 'new';
                        this.newPin = '';
                        this.currentPin = '';
                    }
                }
            },

            async submitPin() {
                try {
                    const form = document.getElementById('pinForm');
                    const formData = new FormData(form);

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json, text/html, */*',
                        },
                        body: formData,
                    });

                    if (res.ok || res.redirected) {
                        this.isSuccess = true;
                        // Auto-close after 2s
                        setTimeout(() => {
                            this.resetAll();
                            this.open = false;
                        }, 2000);
                    } else {
                        this.triggerError('Gagal menyimpan PIN. Coba lagi.');
                        this.step = 'new';
                        this.newPin = '';
                        this.currentPin = '';
                    }
                } catch (e) {
                    // Fallback: submit form normally
                    document.getElementById('pinForm').submit();
                }
            },

            triggerError(msg) {
                this.isError = true;
                this.errorMsg = msg;
                this.hasShake = true;
                setTimeout(() => { this.hasShake = false; }, 400);
            },

            resetAll() {
                this.step = 'new';
                this.currentPin = '';
                this.newPin = '';
                this.confirmedPin = '';
                this.isError = false;
                this.isSuccess = false;
                this.errorMsg = '';
                this.hasShake = false;
            }
        };
    }
</script>
@endsection
