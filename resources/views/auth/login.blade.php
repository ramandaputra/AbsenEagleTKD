<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Masuk ke Sistem - Eagle Taekwondo</title>
    
    <!-- App Icons -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#00288E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eagle Taekwondo">

    <!-- Google Fonts: Inter & Material Symbols Rounded -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brandPrimary: '#00288E',
                        brandPrimaryContainer: '#1E40AF',
                        brandSecondary: '#0051D5',
                        brandBg: '#FAF8FF',
                        surfaceHighest: '#DAE2FD',
                        textOnSurface: '#131B2E',
                        textVariant: '#64748B',
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #131B2E;
            margin: 0;
            padding: 0;
            user-select: none;
            -webkit-user-select: none;
        }

        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: 500;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            display: inline-block;
            white-space: nowrap;
            vertical-align: middle;
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            -webkit-font-smoothing: antialiased;
        }

        /* Pure Circular Keypad Button (Tirtafield 66px Specs) */
        .keypad-btn {
            width: 66px;
            height: 66px;
            border-radius: 9999px;
            background-color: #ffffff;
            border: 1.2px solid #DAE2FD;
            color: #131B2E;
            font-size: 26px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: transform 0.1s ease, background-color 0.1s ease, border-color 0.1s ease;
            user-select: none;
        }
        .keypad-btn:active {
            transform: scale(0.91);
            background-color: #EAEDFF;
            border-color: #316BF3;
        }

        /* Error Shake Animation */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }
        .animate-shake {
            animation: shake 0.35s ease-in-out;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#131B2E] min-h-screen antialiased flex flex-col items-center justify-center selection:bg-blue-100">

    <!-- Mobile-First App Shell (Max width 430px centered, zero-scroll 100vh height) -->
    <div x-data="flutterLoginApp()" x-init="init()"
         class="w-full max-w-[430px] h-screen sm:h-[100dvh] bg-[#FAF8FF] shadow-[0_0_30px_rgba(0,0,0,0.06)] sm:border-x sm:border-[#DAE2FD] relative flex flex-col justify-between py-4 px-5 select-none overflow-hidden">

        <!-- Top Header & App Branding -->
        <div class="flex flex-col items-center text-center pt-2">
            <a href="{{ route('web.splash') }}" title="Lihat Splash Screen"
               class="w-[60px] h-[60px] rounded-2xl overflow-hidden border border-[#DAE2FD] shadow-md shadow-[#00288E]/10 flex items-center justify-center hover:scale-105 transition-transform bg-white mb-2">
                <img src="{{ asset('images/icon-eagle.jpg') }}" alt="Logo Eagle TKD" class="w-full h-full object-cover rounded-2xl"
                     onerror="this.style.display='none'; document.getElementById('loginFallbackIcon').style.display='block';">
                <div id="loginFallbackIcon" style="display: none;" class="text-[#00288E] flex items-center justify-center">
                    <span class="material-symbols-rounded text-3xl">sports_martial_arts</span>
                </div>
            </a>
            <h1 class="text-[19px] font-extrabold text-[#131B2E] tracking-tight leading-none font-sans">
                EAGLE TAEKWONDO
            </h1>
            <p class="text-[11.5px] text-[#64748B] mt-1 font-medium">
                Presensi & Penjadwalan Pelatih
            </p>
        </div>

        <!-- Center Interactive Area: Name Input + PIN Dots + Keypad -->
        <div class="flex flex-col items-center justify-center w-full my-auto">

            <!-- Name Input Field -->
            <div class="relative w-full max-w-[320px] mb-3">
                <label class="block text-[10px] font-bold tracking-wider uppercase text-[#64748B] mb-1 pl-1">Nama Pelatih</label>
                <div class="relative flex items-center">
                    <div class="absolute left-3 w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-sm transition-colors"
                         :class="nameInput ? 'bg-[#00288E]' : 'bg-[#CBD5E1]'"
                         x-text="nameInput ? nameInput.charAt(0).toUpperCase() : '?'">
                    </div>
                    <input
                        type="text"
                        x-model="nameInput"
                        @input="onNameInput()"
                        @keydown.enter.prevent="nameInput && pin.length === 4 && executeLogin()"
                        placeholder="Ketik nama Anda..."
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        class="w-full pl-11 pr-4 py-3 bg-white border border-[#DAE2FD] rounded-2xl shadow-sm text-sm font-semibold text-[#131B2E] placeholder-[#94A3B8] focus:outline-none focus:border-[#00288E] focus:ring-2 focus:ring-[#00288E]/10 transition-all"
                    >
                </div>
                <!-- Autocomplete suggestions -->
                <div x-show="suggestions.length > 0 && showSuggestions" @click.away="showSuggestions = false" x-cloak
                     class="absolute top-full left-0 right-0 mt-1 bg-white border border-[#DAE2FD] rounded-2xl shadow-xl z-50 overflow-hidden">
                    <template x-for="u in suggestions" :key="u.id">
                        <button type="button" @click="selectSuggestion(u)"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left hover:bg-[#F2F3FF] transition-colors border-b border-[#F1F5F9] last:border-0">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs text-white shrink-0"
                                 :class="u.role.includes('superadmin') ? 'bg-amber-600' : (u.role.includes('admin') ? 'bg-red-600' : 'bg-blue-600')"
                                 x-text="u.name.charAt(0)">
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#131B2E]" x-text="u.name"></p>
                                <p class="text-[10px] text-[#64748B]" x-text="u.role_display"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- PIN Keamanan Header & 4 Animated Dots -->
            <div class="w-full max-w-[320px] text-center mb-2">
                <div class="text-[11px] font-bold tracking-wider uppercase text-[#64748B] mb-2 font-sans">
                    PIN Keamanan (4 Digit)
                </div>

                <!-- 4 Animated PIN Dots -->
                <div :class="{ 'animate-shake': hasErrorShake }" class="flex items-center justify-center space-x-4 my-1">
                    <template x-for="i in 4" :key="i">
                        <div class="w-4 h-4 rounded-full transition-all duration-150"
                             :class="errorMessage ? 'bg-[#DC2626] border-2 border-[#DC2626]' : (i <= pin.length ? 'bg-[#00288E] border-2 border-[#00288E] shadow-[0_0_8px_rgba(0,40,142,0.35)] scale-110' : 'bg-white border-2 border-[#CBD5E1]')">
                        </div>
                    </template>
                </div>

                <!-- Feedback / Error / Loading Message (Fixed Height 22px to prevent layout shift) -->
                <div class="h-[22px] flex items-center justify-center mt-1">
                    <template x-if="isLoading">
                        <div class="flex items-center space-x-1.5 text-xs font-semibold text-[#0051D5]">
                            <div class="w-3.5 h-3.5 border-2 border-[#0051D5] border-t-transparent rounded-full animate-spin"></div>
                            <span>Memverifikasi kredensial...</span>
                        </div>
                    </template>
                    <template x-if="!isLoading && errorMessage">
                        <div class="text-xs font-bold text-[#DC2626]" x-text="errorMessage"></div>
                    </template>
                    <template x-if="!isLoading && !errorMessage && pin.length === 0">
                        <span class="text-[11px] text-[#64748B]">Ketik 4 digit PIN Anda</span>
                    </template>
                </div>
            </div>

            <!-- Pure Numeric Keypad (3x4 Grid of Circular Round Buttons - Tirtafield Exact Specs) -->
            <div class="w-full max-w-[320px] mb-2">
                <div class="grid grid-cols-3 gap-y-2.5 gap-x-5 justify-items-center">
                    <!-- Row 1 -->
                    <button type="button" @click="pressDigit('1')" class="keypad-btn">1</button>
                    <button type="button" @click="pressDigit('2')" class="keypad-btn">2</button>
                    <button type="button" @click="pressDigit('3')" class="keypad-btn">3</button>
                    <!-- Row 2 -->
                    <button type="button" @click="pressDigit('4')" class="keypad-btn">4</button>
                    <button type="button" @click="pressDigit('5')" class="keypad-btn">5</button>
                    <button type="button" @click="pressDigit('6')" class="keypad-btn">6</button>
                    <!-- Row 3 -->
                    <button type="button" @click="pressDigit('7')" class="keypad-btn">7</button>
                    <button type="button" @click="pressDigit('8')" class="keypad-btn">8</button>
                    <button type="button" @click="pressDigit('9')" class="keypad-btn">9</button>
                    <!-- Row 4 -->
                    <div class="w-[66px] h-[66px] flex items-center justify-center">
                        <button type="button" @click="clearPin()" class="text-xs font-bold text-[#64748B] hover:text-[#131B2E] py-2 px-3 rounded-lg active:scale-95 transition-transform" title="Bersihkan PIN">
                            C
                        </button>
                    </div>
                    <button type="button" @click="pressDigit('0')" class="keypad-btn">0</button>
                    <button type="button" @click="pressBackspace()" class="keypad-btn text-[#131B2E] flex items-center justify-center" title="Hapus">
                        <span class="material-symbols-rounded text-2xl">backspace</span>
                    </button>
                </div>
            </div>

            <!-- Demo Hint Pill -->
            <div class="w-full max-w-[320px] px-3 py-1.5 rounded-xl bg-[#F2F3FF] border border-[#DAE2FD] text-center text-[10.5px] text-[#00288E]">
                💡 Gunakan nama: <span class="font-bold">mingle</span>, <span class="font-bold">Lukman</span>, atau <span class="font-bold">Raja</span> • PIN <span class="font-bold">1234</span>
            </div>

        </div>

        <!-- Discrete Footer -->
        <div class="w-full text-center pb-1 text-[10.5px] text-[#64748B] shrink-0">
            <span>Sistem Informasi Absensi & Penjadwalan Pelatih • v2.0</span>
        </div>

        <!-- Hidden Fallback Form for Standard Form Submission -->
        <form id="hiddenLoginForm" action="{{ route('login.pin') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="name" :value="nameInput">
            <input type="hidden" name="pin" :value="pin">
        </form>

    </div>

@php
    $usersData = $demoUsers->map(function($u) {
        return [
            'id'           => $u->id,
            'name'         => $u->name,
            'role'         => $u->role_name ?? '',
            'role_display' => $u->role_display_name ?? 'Pelatih',
            'belt_level'   => $u->belt_level ?? 'Dan Kukkiwon',
        ];
    })->values();
@endphp

    <!-- Alpine.js Application Logic -->
    <script>
        function flutterLoginApp() {
            return {
                users: {!! json_encode($usersData) !!},
                nameInput: '',
                suggestions: [],
                showSuggestions: false,
                pin: '',
                isLoading: false,
                errorMessage: null,
                hasErrorShake: false,

                init() {
                    // Physical keyboard listener (0-9, Backspace)
                    window.addEventListener('keydown', (e) => {
                        // Only handle numpad when focus is NOT on the name input
                        if (document.activeElement && document.activeElement.type === 'text') return;
                        if (e.key >= '0' && e.key <= '9') {
                            this.pressDigit(e.key);
                        } else if (e.key === 'Backspace') {
                            this.pressBackspace();
                        }
                    });
                },

                onNameInput() {
                    this.errorMessage = null;
                    this.pin = '';
                    const q = this.nameInput.trim().toLowerCase();
                    if (q.length === 0) {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }
                    this.suggestions = this.users.filter(u => u.name.toLowerCase().startsWith(q)).slice(0, 5);
                    this.showSuggestions = this.suggestions.length > 0;
                },

                selectSuggestion(u) {
                    this.nameInput = u.name;
                    this.suggestions = [];
                    this.showSuggestions = false;
                    this.pin = '';
                    this.errorMessage = null;
                },

                pressDigit(d) {
                    if (this.isLoading) return;

                    if (!this.nameInput.trim()) {
                        this.errorMessage = 'Silakan masukkan nama terlebih dahulu.';
                        return;
                    }

                    if (this.pin.length < 4) {
                        this.errorMessage = null;
                        this.pin += d;

                        if (this.pin.length === 4) {
                            this.executeLogin();
                        }
                    }
                },

                pressBackspace() {
                    if (this.isLoading) return;
                    if (this.pin.length > 0) {
                        this.errorMessage = null;
                        this.pin = this.pin.substring(0, this.pin.length - 1);
                    }
                },

                clearPin() {
                    if (this.isLoading) return;
                    this.pin = '';
                    this.errorMessage = null;
                },

                async executeLogin() {
                    if (!this.nameInput.trim()) {
                        this.errorMessage = 'Silakan masukkan nama.';
                        this.pin = '';
                        return;
                    }

                    if (this.pin.length !== 4) {
                        this.errorMessage = 'PIN harus terdiri dari 4 angka.';
                        return;
                    }

                    this.isLoading = true;
                    this.errorMessage = null;

                    try {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('name', this.nameInput.trim());
                        formData.append('pin', this.pin);

                        const res = await fetch("{{ route('login.pin') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json, text/html, */*',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        });

                        this.isLoading = false;

                        if (res.ok) {
                            const json = await res.json().catch(() => ({}));
                            window.location.href = json.redirect || "{{ route('dashboard') }}";
                        } else {
                            let errText = 'Nama atau PIN yang Anda masukkan tidak sesuai.';
                            try {
                                const json = await res.json();
                                if (json.message) { errText = json.message; }
                            } catch (e) {}
                            this.triggerShakeError(errText);
                        }
                    } catch (e) {
                        this.isLoading = false;
                        document.getElementById('hiddenLoginForm').submit();
                    }
                },

                triggerShakeError(msg) {
                    this.errorMessage = msg;
                    this.pin = '';
                    this.hasErrorShake = true;
                    setTimeout(() => { this.hasErrorShake = false; }, 400);
                }
            };
        }
    </script>
</body>
</html>
