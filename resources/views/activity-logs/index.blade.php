@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="space-y-3.5 pb-20">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl border border-[#DAE2FD] p-3.5 shadow-xs flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('dashboard') }}"
               class="w-9 h-9 rounded-full bg-[#EAEDFF] text-[#131B2E] hover:bg-[#DAE2FD] flex items-center justify-center transition-colors shrink-0">
                <span class="material-symbols-rounded text-xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-[#131B2E] truncate">Log Aktivitas Sistem</h1>
                <p class="text-[11px] text-[#64748B] truncate mt-0.5">Audit trail pencatatan aktivitas pengguna</p>
            </div>
        </div>

        <span class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
            <span class="material-symbols-rounded text-xl">history</span>
        </span>
    </div>

    <!-- Filter Action Form -->
    <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3 shadow-xs">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex items-center gap-2 text-xs">
            <select name="action" class="flex-1 py-2 px-3 rounded-xl bg-[#FAF8FF] border border-[#DAE2FD] text-[#131B2E] focus:outline-none focus:border-[#0051D5]">
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
                <option value="EXPORT_EXCEL" {{ request('action') === 'EXPORT_EXCEL' ? 'selected' : '' }}>EXPORT_EXCEL</option>
                <option value="EXPORT_PDF" {{ request('action') === 'EXPORT_PDF' ? 'selected' : '' }}>EXPORT_PDF</option>
            </select>
            <button type="submit" class="py-2 px-3.5 rounded-xl bg-[#0051D5] hover:bg-[#00288E] text-white font-bold transition-colors shrink-0">
                Filter
            </button>
        </form>
    </div>

    <!-- Logs List -->
    <div class="space-y-2.5">
        @forelse($logs as $log)
            <div class="bg-white border border-[#DAE2FD] rounded-2xl p-3 shadow-xs space-y-1.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="text-[9px] font-mono px-2 py-0.5 rounded-full font-bold
                            {{ $log->action === 'LOGIN' ? 'bg-blue-50 text-blue-700 border border-blue-200' :
                              ($log->action === 'CHECK_IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                               ($log->action === 'DELETE_SCHEDULE' || $log->action === 'DELETE_USER' ? 'bg-rose-50 text-rose-700 border border-rose-200' :
                                'bg-amber-50 text-amber-700 border border-amber-200')) }}">
                            {{ $log->action }}
                        </span>
                        <strong class="text-xs text-[#131B2E] truncate">{{ $log->user ? $log->user->name : 'Sistem' }}</strong>
                    </div>
                    <span class="text-[10px] text-[#64748B] font-mono shrink-0">
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <p class="text-xs text-[#475569] leading-relaxed">{{ $log->description }}</p>
                <p class="text-[10px] text-[#94A3B8] font-mono">
                    IP: {{ $log->ip_address ?: '127.0.0.1' }} &bull; {{ $log->created_at->diffForHumans() }}
                </p>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-[#DAE2FD] p-6 text-center text-[#64748B]">
                <span class="material-symbols-rounded text-3xl text-slate-300">history_toggle_off</span>
                <p class="text-xs font-semibold text-[#131B2E] mt-1">Belum Ada Catatan Log Aktivitas</p>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
