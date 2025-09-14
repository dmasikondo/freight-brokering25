<?php

namespace App\Enums;

enum PricingType: string
{
    case FullBudget = 'full_budget';
    case RateOfCarriage = 'rate_of_carriage';
}
