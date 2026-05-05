<?php

namespace App\Enums;

enum PricingType: string
{
    case FullBudget = 'full_budget';
    case RateOfCarriage = 'rate_of_carriage';

    public function label(): string
    {
        return match ($this) {
            self::FullBudget  => 'Flat Rate',
            self::RateOfCarriage => 'Rate of Carriage',
        };
    }    
}
