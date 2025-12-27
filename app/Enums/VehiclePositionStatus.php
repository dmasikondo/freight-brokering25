<?php

namespace App\Enums;

enum VehiclePositionStatus: string
{
    case LOADING = 'loading';
    case INTRANSIT = 'in transit';
    case DELIVERED = 'delivered';
    case INAPPLICABLE = 'inapplicable';
    case NOT_CONTRACTED = 'ready';


    public function label(): string
    {
        return match ($this){
            self::LOADING => 'loading',
            self::INTRANSIT => 'in transit',
            self::DELIVERED => 'delivered',
            self::INAPPLICABLE => 'unknown',
            self::NOT_CONTRACTED => 'ready',

        };
    }
}   