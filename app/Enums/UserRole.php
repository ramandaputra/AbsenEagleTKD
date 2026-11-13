<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin'; // Pelatih Utama
    case AsistenPelatih = 'asisten_pelatih';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin (Pimpinan/Owner)',
            self::Admin => 'Admin (Pelatih Utama / Sabeum Nim)',
            self::AsistenPelatih => 'Pengguna (Asisten Pelatih)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Superadmin => 'bg-red-900/60 text-red-300 border-red-700',
            self::Admin => 'bg-amber-900/60 text-amber-300 border-amber-700',
            self::AsistenPelatih => 'bg-blue-900/60 text-blue-300 border-blue-700',
        };
    }
}

