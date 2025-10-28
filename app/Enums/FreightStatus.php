<?php

namespace App\Enums;

enum FreightStatus: string
{
    case SUBMITTED = 'submitted';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case DRAFT = 'draft';  
    case EXPIRED ='0'; 

    public function label(): string
    {
        return match ($this){
            self::SUBMITTED => 'Pending',
            self::PUBLISHED => 'Published',
            self::UNPUBLISHED => 'Unpublished',
            self::DRAFT => 'Draft',
            self::EXPIRED => 'No longer Available',
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
        };
    }
}
