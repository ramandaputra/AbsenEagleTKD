<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#00288E">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Eagle Taekwondo') - Presensi & Penjadwalan Pelatih</title>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-eagle.jpg') }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- Google Material Symbols Rounded (Matching Tirtafield-Web) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Tailwind CSS with Exact Tirtafield AppColors -->
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
                        brandSecondaryContainer: '#316BF3',
                        brandBg: '#FAF8FF',
                        brandSurface: '#FAF8FF',
                        surfaceLow: '#F2F3FF',
                        surfaceContainer: '#EAEDFF',
                        surfaceHigh: '#E2E7FF',
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

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

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
        }
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: 500;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            vertical-align: middle;
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            -webkit-font-smoothing: antialiased;
        }
        .material-symbols-rounded.fill {
            font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 24;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#131B2E] min-h-screen antialiased flex flex-col items-center justify-start selection:bg-[#0051D5]/20 selection:text-[#0051D5]">

    <!-- Mobile-First App Shell (Max width 430px centered, 100% on mobile devices - Tirtafield Spec) -->
    <div class="w-full max-w-[430px] min-h-screen bg-[#FAF8FF] shadow-[0_0_30px_rgba(0,0,0,0.06)] sm:border-x sm:border-[#DAE2FD] relative flex flex-col pb-24">

        @auth
        <!-- Top App Bar (Mirrors Tirtafield Top App Bar) -->
        <header class="bg-[#FAF8FF]/95 backdrop-blur-md sticky top-0 z-40 border-b border-[#DAE2FD]/80 shadow-[0_2px_10px_rgba(15,23,42,0.03)] w-full no-print">
            <div class="px-4 py-2.5 flex items-center justify-between w-full">

                @if(request()->routeIs('profile.*'))
                    <!-- Top Header when on Akun / Profile Page -->
                    <div class="flex items-center space-x-2.5 w-full min-w-0">
                        <a href="{{ route('dashboard') }}" class="p-1.5 -ml-1.5 rounded-full text-[#131B2E] hover:bg-[#EAEDFF] flex items-center shrink-0 transition-colors">
                            <span class="material-symbols-rounded text-2xl">arrow_back</span>
                        </a>
                        <div class="truncate">
                            <h1 class="font-bold text-sm sm:text-base text-[#131B2E] truncate leading-tight">Akun & Profil</h1>
                            <p class="text-[11px] text-[#64748B] truncate mt-0.5">{{ auth()->user()->name }} · {{ auth()->user()->role_display_name }}</p>
                        </div>
                    </div>
                @else
                    <!-- Default Main Tab Header (Avatar + Title + Subtitle) -->
                    <div class="flex items-center space-x-3 w-full min-w-0">
                        @if(request()->routeIs('schedules.index') || request()->routeIs('salary.admin.index') || request()->routeIs('attendances.show'))
                            <a href="{{ route('dashboard') }}"
                               onclick="if (document.referrer && new URL(document.referrer).origin === window.location.origin) { window.history.back(); return false; }"
                               aria-label="Kembali" title="Kembali"
                               class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[#131B2E] transition-colors hover:bg-[#EAEDFF]">
                                <i class="bi bi-chevron-left text-lg" aria-hidden="true"></i>
                            </a>
                        @endif

                        <!-- Avatar with Online Ring Dot -->
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-full ring-2 ring-[#0051D5]/30 bg-gradient-to-tr from-[#00288E] to-[#0051D5] text-white font-bold flex items-center justify-center text-xs shadow-xs overflow-hidden shrink-0">
                                @if (auth()->user()->avatar)
                                    <img src="/{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-500 rounded-full border-2 border-white ring-1 ring-emerald-400"></span>
                        </div>

                        <!-- Corporate Title & Subtitle Matching Tirtafield -->
                        <div class="min-w-0 flex-1">
                            <div class="font-extrabold text-[15px] tracking-tight text-[#131B2E] font-sans truncate flex items-center gap-1.5">
                                <span>EAGLE TAEKWONDO</span>
                            </div>
                            <div class="text-[11.5px] text-[#64748B] font-medium truncate">
                                {{ auth()->user()->name }} · {{ auth()->user()->belt_level ?: auth()->user()->role_display_name }}
                            </div>
                        </div>

                        <!-- Quick Logout Icon -->
                        <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" title="Keluar dari Akun" onclick="return confirm('Apakah Anda yakin ingin keluar?')"
                                    class="p-2 rounded-xl text-[#64748B] hover:text-rose-600 hover:bg-rose-50 transition-colors flex items-center justify-center">
                                <span class="material-symbols-rounded text-xl">logout</span>
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </header>

        <!-- Superadmin Navigation Banner -->
        @if(auth()->user()->isSuperAdmin() && !request()->routeIs('profile.*'))
            <div class="bg-[#EAEDFF] border-b border-[#DAE2FD] px-3 py-2 overflow-x-auto no-scrollbar flex items-center gap-2 text-xs no-print">
                <span class="text-[#00288E] font-bold px-2 py-0.5 rounded-lg bg-white border border-[#DAE2FD] shrink-0 flex items-center gap-1">
                    <span class="material-symbols-rounded text-sm">shield</span> MENU
                </span>
                <a href="{{ route('schedules.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('schedules.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                    Jadwal, Piket & Materi
                </a>
                <a href="{{ route('reports.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                    Laporan & Export
                </a>
                <a href="{{ route('salary.admin.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('salary.admin.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                    Rekap Gaji & Tarif
                </a>
                <a href="{{ route('activity-logs.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('activity-logs.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                    Log Aktivitas
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('users.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                        Kelola Pelatih
                    </a>
                    <a href="{{ route('dojangs.index') }}" class="px-2.5 py-1 rounded-lg font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('dojangs.*') ? 'bg-[#00288E] text-white shadow-xs' : 'bg-white text-[#131B2E] border border-[#DAE2FD] hover:bg-[#F2F3FF]' }}">
                        Cabang Dojang
                    </a>
                @endif
            </div>
        @endif
        @endauth

        <!-- Flash Messages (Tirtafield Light Style) -->
        <div class="px-4 pt-3 w-full no-print">
            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-3 rounded-r-xl shadow-xs text-xs font-semibold flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-rounded text-emerald-600 text-lg">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-3 rounded-r-xl shadow-xs text-xs font-semibold flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-rounded text-rose-600 text-lg">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-3 rounded-r-xl shadow-xs text-xs font-semibold mb-2">
                    <div class="flex items-center space-x-1.5 mb-1">
                        <span class="material-symbols-rounded text-rose-600 text-base">warning</span>
                        <span>Perhatian:</span>
                    </div>
                    <ul class="list-disc list-inside text-rose-700 text-[11px] space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Mobile Content Area -->
        <main class="flex-1 w-full px-4 py-3">
            @yield('content')
        </main>

        @auth
        <!-- Floating Bottom Navigation Bar (Matching Tirtafield-Web exactly) -->
        <div class="fixed bottom-3 left-1/2 -translate-x-1/2 w-full max-w-[430px] z-40 px-3 pointer-events-none no-print">
            <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-[#DAE2FD] pointer-events-auto p-1 flex items-center justify-around h-15">

                @if(auth()->user()->isAsistenPelatih() || request()->routeIs('profile.*') || (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()))
                    {{-- Asisten Pelatih and Admin/Pelatih use Dashboard and Akun tabs. --}}
                    <!-- Tab 1: Beranda -->
                    <a href="{{ route('dashboard') }}"
                       class="flex-1 flex flex-col items-center py-1 rounded-xl transition-all relative {{ request()->routeIs('dashboard') ? 'text-[#0051D5] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium' }}">
                        <span class="material-symbols-rounded text-[22px] {{ request()->routeIs('dashboard') ? 'fill' : '' }}">home</span>
                        <span class="text-[11px] mt-0.5">Beranda</span>
                        @if(request()->routeIs('dashboard'))
                            <div class="w-1 h-1 rounded-full bg-[#0051D5] mt-0.5"></div>
                        @endif
                    </a>

                    <!-- Tab 2: Akun -->
                    <a href="{{ route('profile.show') }}"
                       class="flex-1 flex flex-col items-center py-1 rounded-xl transition-all relative {{ request()->routeIs('profile.*') ? 'text-[#0051D5] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium' }}">
                        <span class="material-symbols-rounded text-[22px] {{ request()->routeIs('profile.*') ? 'fill' : '' }}">person</span>
                        <span class="text-[11px] mt-0.5">Akun</span>
                        @if(request()->routeIs('profile.*'))
                            <div class="w-1 h-1 rounded-full bg-[#0051D5] mt-0.5"></div>
                        @endif
                    </a>

                @else
                    {{-- SUPERADMIN: Full Navigation Tabs --}}
                    <!-- Beranda -->
                    <a href="{{ route('dashboard') }}" class="flex-1 flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-[#00288E] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium' }}">
                        <span class="material-symbols-rounded text-[20px] {{ request()->routeIs('dashboard') ? 'fill' : '' }}">home</span>
                        <span class="text-[10px] mt-0.5">Beranda</span>
                        @if(request()->routeIs('dashboard'))
                            <div class="w-1 h-1 rounded-full bg-[#00288E] mt-0.5"></div>
                        @endif
                    </a>

                    <!-- Jadwal -->
                    <a href="{{ route('schedules.index') }}" class="flex-1 flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('schedules.*') ? 'text-[#00288E] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium' }}">
                        <span class="material-symbols-rounded text-[20px] {{ request()->routeIs('schedules.*') ? 'fill' : '' }}">calendar_month</span>
                        <span class="text-[10px] mt-0.5">Jadwal</span>
                        @if(request()->routeIs('schedules.*'))
                            <div class="w-1 h-1 rounded-full bg-[#00288E] mt-0.5"></div>
                        @endif
                    </a>

                    <!-- Tombol Check-in Float -->
                    <a href="{{ route('attendances.create') }}" class="flex flex-col items-center -mt-5 group px-1">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#00288E] to-[#316BF3] p-0.5 shadow-md shadow-[#00288E]/30 group-active:scale-95 transition-transform flex items-center justify-center">
                            <div class="w-full h-full rounded-full bg-[#00288E] flex items-center justify-center text-white">
                                <span class="material-symbols-rounded text-xl">photo_camera</span>
                            </div>
                        </div>
                        <span class="text-[9.5px] font-bold text-[#00288E] mt-0.5">Check-in</span>
                    </a>

                    <!-- Akun -->
                    <a href="{{ route('profile.show') }}" class="flex-1 flex flex-col items-center py-1 rounded-xl transition-all {{ request()->routeIs('profile.*') ? 'text-[#00288E] font-bold' : 'text-[#64748B] hover:text-slate-800 font-medium' }}">
                        <span class="material-symbols-rounded text-[20px] {{ request()->routeIs('profile.*') ? 'fill' : '' }}">person</span>
                        <span class="text-[10px] mt-0.5">Akun</span>
                        @if(request()->routeIs('profile.*'))
                            <div class="w-1 h-1 rounded-full bg-[#00288E] mt-0.5"></div>
                        @endif
                    </a>
                @endif

            </div>
        </div>
        @endauth

    </div>

    <!-- Initialize Lucide Icons fallback -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
