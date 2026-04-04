<?php 

namespace App\Enums;

enum PartnerType: string
{
    case SHIPPER = 'shipper';
    case CARRIER = 'carrier';
    case CONSULTANCY = 'consultancy';
    case GENERAL = 'general';

    public function label(): string
    {
        return match($this) {
            self::SHIPPER => 'Shipper (Lead)',
            self::CARRIER => 'Carrier (Fleet)',
            self::CONSULTANCY => 'Consultancy',
            self::GENERAL => 'General Partner',
        };
    }
}