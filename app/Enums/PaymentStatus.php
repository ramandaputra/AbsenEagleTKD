<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Perhitungan',
            self::Approved => 'Disetujui',
            self::Paid => 'Lunas / Dibayar',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-500/20 text-slate-300 border border-slate-500/30',
            self::Approved => 'bg-blue-500/20 text-blue-400 border border-blue-500/30',
            self::Paid => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
        };
    }
}

