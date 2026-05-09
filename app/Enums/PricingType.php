<?php

namespace App\Enums;

enum PricingType: string
{
    case FullBudget = 'full_budget';
    case RateOfCarriage = 'rate_of_carriage';
    case CashOnDelivery = 'Cash on Delivery';
    case ProofOfDelivery = 'Proof of Delivery';
    case HalfDownPayment = 'Half Down Payment';

    public function label(): string
    {
        return match ($this) {
            self::FullBudget  => 'Flat Rate',
            self::RateOfCarriage => 'Rate of Carriage',
            self::CashOnDelivery  => 'Cash on Delivery',
            self::ProofOfDelivery  => 'Proof of Delivery',
            self::HalfDownPayment  => 'Half Down Payment',
        };
    }    
}
