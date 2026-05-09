<?php

namespace App\Enums;

enum TenderOfferStatus: string
{
    case PENDING      = 'pending';
    case SHORTLISTED  = 'shortlisted';
    case AWARDED      = 'awarded';
    case REJECTED     = 'rejected';
    case WITHDRAWN    = 'withdrawn';
    case EXPIRED      = 'expired';

    public function label(): string
    {
        return match($this) {
            self::PENDING     => 'Pending',
            self::SHORTLISTED => 'Shortlisted',
            self::AWARDED     => 'Awarded',
            self::REJECTED    => 'Rejected',
            self::WITHDRAWN   => 'Withdrawn',
            self::EXPIRED     => 'Expired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING     => 'amber',
            self::SHORTLISTED => 'cyan',
            self::AWARDED     => 'green',
            self::REJECTED    => 'red',
            self::WITHDRAWN   => 'zinc',
            self::EXPIRED     => 'rose',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::SHORTLISTED]);
    }
}