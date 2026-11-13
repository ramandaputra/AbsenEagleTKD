<?php

namespace App\Enums;

enum LocationStatus: string
{
    case InRadius = 'in_radius';
    case OutsideRadius = 'outside_radius';
    case GpsDisabled = 'gps_disabled';

    public function label(): string
    {
        return match ($this) {
            self::InRadius => 'Dalam Radius Dojang',
            self::OutsideRadius => 'Di Luar Radius Dojang',
            self::GpsDisabled => 'GPS Tidak Aktif',
        };
    }
}

