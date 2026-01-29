<?php

namespace App\Enums;

enum CapacityUnit: string
{
    case TONNES = 'tonnes';
    case LITRES = 'litres';
    
    public function label(): string
    {
        return match ($this) {
            self::TONNES  => 'Tonnes',
            self::TONNES  => 'Litres',
        };
    }
}
