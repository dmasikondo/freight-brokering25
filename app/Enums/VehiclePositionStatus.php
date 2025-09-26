<?php

namespace App\Enums;

enum VehiclePositionStatus: string
{
    case LOADING = 'loading';
    case INTRANSIT = 'in transit';
    case DELIVERED = 'delivered';
    case INAPPLICABLE = 'inapplicable';
}
