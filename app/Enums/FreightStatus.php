<?php

namespace App\Enums;

enum FreightStatus: string
{
    case SUBMITTED = 'submitted';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case DRAFT = 'draft';   
}
