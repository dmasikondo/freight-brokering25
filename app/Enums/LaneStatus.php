<?php

namespace App\Enums;

enum LaneStatus: string
{
    case SUBMITTED = 'submitted';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case DRAFT = 'draft';   
    case EXPIRED='0';
    case UNAVAILABLE='1';
    case CEASED='2';

    public function label(): string
    {
        return match ($this){
            self::SUBMITTED => 'Pending',
            self::PUBLISHED => 'Published',
            self::UNPUBLISHED => 'Unpublished',
            self::DRAFT => 'Draft',
            self::EXPIRED => 'Expired',
            self::UNAVAILABLE => 'Expired',
            self::CEASED => 'Expired',

        };
    }

    public function color(): string
    {
        return match ($this){
            self::SUBMITTED => 'amber',
            self::PUBLISHED => 'teal',
            self::UNPUBLISHED =>'fuchsia',
            self::DRAFT => 'zinc',
            self::EXPIRED => 'red',
            self::CEASED => 'red',
            self::UNAVAILABLE => 'red',
        };
    }
}
