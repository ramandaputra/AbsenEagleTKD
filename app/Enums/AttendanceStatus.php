<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
            self::Approved => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
            self::Rejected => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
        };
    }
}

