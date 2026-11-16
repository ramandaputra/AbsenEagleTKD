@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Executive Header Banner (Matching Schedules & Salary Style) -->
    <section class="relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-br from-[#00227A] via-[#00144D] to-[#070E28] p-4 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-5 -top-5 h-36 w-36 rounded-full bg-blue-500/20 blur-xl"></div>
        <div class="pointer-events-none absolute -bottom-6 -left-6 h-32 w-32 rounded-full bg-cyan-400/10 blur-xl"></div>

        <div class="relative z-10 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full border border-blue-300/40 bg-blue-500/25 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-100">
                    Audit Trail &amp; Keamanan
                </span>
                <h1 class="mt-1.5 text-sm font-extrabold tracking-tight">Log Aktivitas Sistem</h1>
                <p class="mt-0.5 text-[11px] font-medium text-[#7DD3FC]">Rekam jejak aktivitas, presensi, jadwal, dan perubahan data.</p>
            </div>

            <div class="flex items-center gap-1.5 text-[11px] text-white/90 font-mono bg-white/10 px-3 py-1.5 rounded-xl border border-white/20 shrink-0">
                <span class="material-symbols-rounded text-base text-[#7DD3FC]">history</span>
                <span>{{ $logs->total() }} Log</span>
            </div>
        </div>
    </section>

    <!-- Filter Action Form Card -->
    <section class="rounded-xl border border-[#DAE2FD]/70 bg-white p-2.5 shadow-2xs">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex items-center gap-2 text-xs">
            <select name="action" class="flex-1 py-2 px-3 rounded-lg bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] font-medium focus:outline-none focus:border-[#0051D5]">
                <option value="">Semua Aksi</option>
                <option value="LOGIN" {{ request('action') === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
                <option value="LOGOUT" {{ request('action') === 'LOGOUT' ? 'selected' : '' }}>LOGOUT</option>
                <option value="CHECK_IN" {{ request('action') === 'CHECK_IN' ? 'selected' : '' }}>CHECK_IN</option>
                <option value="CREATE_USER" {{ request('action') === 'CREATE_USER' ? 'selected' : '' }}>CREATE_USER</option>
                <option value="UPDATE_USER" {{ request('action') === 'UPDATE_USER' ? 'selected' : '' }}>UPDATE_USER</option>
                <option value="DELETE_USER" {{ request('action') === 'DELETE_USER' ? 'selected' : '' }}>DELETE_USER</option>
                <option value="CREATE_SCHEDULE" {{ request('action') === 'CREATE_SCHEDULE' ? 'selected' : '' }}>CREATE_SCHEDULE</option>
                <option value="UPDATE_SCHEDULE" {{ request('action') === 'UPDATE_SCHEDULE' ? 'selected' : '' }}>UPDATE_SCHEDULE</option>
                <option value="DELETE_SCHEDULE" {{ request('action') === 'DELETE_SCHEDULE' ? 'selected' : '' }}>DELETE_SCHEDULE</option>
                <option value="UPDATE_ATTENDANCE" {{ request('action') === 'UPDATE_ATTENDANCE' ? 'selected' : '' }}>UPDATE_ATTENDANCE</option>
                <option value="UPDATE_RATE" {{ request('action') === 'UPDATE_RATE' ? 'selected' : '' }}>UPDATE_RATE</option>
                <option value="GENERATE_PAYROLL" {{ request('action') === 'GENERATE_PAYROLL' ? 'selected' : '' }}>GENERATE_PAYROLL</option>
                <option value="EXPORT_EXCEL" {{ request('action') === 'EXPORT_EXCEL' ? 'selected' : '' }}>EXPORT_EXCEL</option>
                <option value="EXPORT_PDF" {{ request('action') === 'EXPORT_PDF' ? 'selected' : '' }}>EXPORT_PDF</option>
            </select>
            <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#0051D5] hover:bg-[#00288E] text-white font-bold transition-colors">
                Filter
            </button>
            @if(request('action'))
                <a href="{{ route('activity-logs.index') }}" class="px-2.5 py-2 rounded-lg bg-[#EAEDFF] text-[#0051D5] font-bold text-xs hover:bg-[#DAE2FD]">
                    Reset
                </a>
            @endif
        </form>
    </section>

    <!-- Activity Log List -->
    <section class="space-y-2.5">
        @forelse($logs as $log)
            @php
                $badgeClasses = match($log->action) {
                    'LOGIN' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'CHECK_IN' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'DELETE_USER', 'DELETE_SCHEDULE' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'CREATE_USER', 'CREATE_SCHEDULE' => 'bg-purple-50 text-purple-700 border-purple-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                };
            @endphp
            <article class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeClasses }}">
                            {{ $log->action }}
                        </span>
                        <span class="text-xs font-bold text-[#131B2E] truncate">
                            {{ $log->user?->name ?? 'Sistem Otomatis' }}
                        </span>
                    </div>

                    <span class="text-[10px] text-[#64748B] shrink-0 font-mono">
                        {{ $log->created_at->format('d/m/Y H:i') }} WIB
                    </span>
                </div>

                <p class="text-xs text-[#475569] leading-relaxed">
                    {{ $log->description }}
                </p>

                <div class="flex items-center justify-between pt-1 border-t border-[#EAEDFF] text-[10px] text-[#94A3B8]">
                    <span>{{ $log->created_at->diffForHumans() }}</span>
                    @if($log->ip_address)
                        <span class="font-mono">IP: {{ $log->ip_address }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">history_toggle_off</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Log Aktivitas</p>
                <p class="text-[10.5px] text-[#64748B] mt-0.5">Semua aksi pengguna akan terekam secara otomatis di sini.</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $logs->links() }}
        </div>
    </section>

</div>
@endsection
