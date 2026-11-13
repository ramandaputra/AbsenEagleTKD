@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="space-y-4">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-white flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-5 h-5 text-red-500"></i> Log Aktivitas Sistem
            </h1>
            <p class="text-xs text-slate-400">Audit trail pencatatan aktivitas pengguna dan aksi administratif</p>
        </div>
    </div>

    <!-- Filter Action -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 shadow-lg">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="flex items-center gap-2 text-xs">
            <select name="action" class="flex-1 py-1.5 px-3 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:border-red-500">
                <option value="">Semua Aksi</option>
                <option value="LOGIN" {{ request('action') === 'LOGIN' ? 'selected' : '' }}>LOGIN</option>
                <option value="LOGOUT" {{ request('action') === 'LOGOUT' ? 'selected' : '' }}>LOGOUT</option>
                <option value="CHECK_IN" {{ request('action') === 'CHECK_IN' ? 'selected' : '' }}>CHECK_IN</option>
                <option value="CREATE_SCHEDULE" {{ request('action') === 'CREATE_SCHEDULE' ? 'selected' : '' }}>CREATE_SCHEDULE</option>
                <option value="UPDATE_SCHEDULE" {{ request('action') === 'UPDATE_SCHEDULE' ? 'selected' : '' }}>UPDATE_SCHEDULE</option>
                <option value="DELETE_SCHEDULE" {{ request('action') === 'DELETE_SCHEDULE' ? 'selected' : '' }}>DELETE_SCHEDULE</option>
                <option value="EXPORT_EXCEL" {{ request('action') === 'EXPORT_EXCEL' ? 'selected' : '' }}>EXPORT_EXCEL</option>
                <option value="EXPORT_PDF" {{ request('action') === 'EXPORT_PDF' ? 'selected' : '' }}>EXPORT_PDF</option>
            </select>
            <button type="submit" class="py-1.5 px-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold transition-colors">
                Filter
            </button>
        </form>
    </div>

    <!-- Logs List -->
    <div class="space-y-2.5">
        @forelse($logs as $log)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3.5 shadow-md flex items-start justify-between gap-3 text-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-mono px-2 py-0.5 rounded-full font-bold
                            {{ $log->action === 'LOGIN' ? 'bg-blue-950 text-blue-300 border border-blue-800' :
                              ($log->action === 'CHECK_IN' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' :
                               ($log->action === 'DELETE_SCHEDULE' ? 'bg-rose-950 text-rose-300 border border-rose-800' :
                                'bg-amber-950 text-amber-300 border border-amber-800')) }}">
                            {{ $log->action }}
                        </span>
                        <strong class="text-white">{{ $log->user ? $log->user->name : 'Sistem' }}</strong>
                    </div>
                    <p class="text-slate-300 mt-1.5">{{ $log->description }}</p>
                    <p class="text-[10px] text-slate-500 font-mono mt-1">
                        IP: {{ $log->ip_address ?: '-' }} • {{ $log->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </span>
            </div>
        @empty
            <p class="text-xs text-slate-500 text-center py-6">Belum ada catatan aktivitas.</p>
        @endforelse

        <div class="pt-2">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection

