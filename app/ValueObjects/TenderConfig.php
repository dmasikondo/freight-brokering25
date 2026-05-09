<?php

namespace App\ValueObjects;

class TenderConfig
{
    public function __construct(
        public readonly string $bidderRole,
        public readonly string $rankOrder,        // 'asc' or 'desc'
        public readonly string $floorField,        // field on tenderable for minimum amount
        public readonly bool   $requiresPickupDate,
        public readonly string $awardPermission,
    ) {}
}