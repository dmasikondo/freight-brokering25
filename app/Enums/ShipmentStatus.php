<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case LOADING = 'loading';
    case INTRANSIT = 'in transit';
    case DELIVERED = 'delivered';
    case INAPPLICABLE = 'inapplicable';
}
