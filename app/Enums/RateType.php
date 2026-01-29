<?php

namespace App\Enums;

enum RateType: string
{
    case PER_KM = 'per_km';
    case FLAT_RATE = 'flat_rate';


    public function label(): string
    {
        return match ($this) {
            self::PER_KM  => 'per km',
            self::FLAT_RATE => 'Flat Rate',
        };
    }
}
