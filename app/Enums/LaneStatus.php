<?php

namespace App\Enums;

enum LaneStatus: string
{
    case SUBMITTED = 'submitted';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case DRAFT = 'draft';   

    public function label(): string
    {
        return match ($this){
            self::SUBMITTED => 'Pending',
            self::PUBLISHED => 'Published',
            self::UNPUBLISHED => 'Unpublished',
            self::DRAFT => 'Draft',
        };
    }

    public function color(): string
    {
        return match ($this){
            self::SUBMITTED => 'amber',
            self::PUBLISHED => 'teal',
            self::UNPUBLISHED =>'fuchsia',
            self::DRAFT => 'zinc',
        };
    }
}
