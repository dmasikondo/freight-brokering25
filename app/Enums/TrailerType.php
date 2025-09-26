<?php

namespace App\Enums;

enum TrailerType: string
{
    case AIR_RIDE_VAN = 'air ride van';
    case DRY_BULK ='dry bulk';
    case DUMP ='dump';
    case CAR_CARRYING ='car carrying';
    case CURTAIN_SIDE = 'curtain side';
    case DROP_DECKER = 'drop decker';
    case DOUBLE_DECKER = 'double decker';
    case FLAT_BED = 'flat bed';
    case HOPPER_BOTTOM = 'hopper bottom';
    case LIVE_BOTTOM = 'live bottom';
    case LIVESTOCK = 'livestock';
    case LOW_BOY = 'low boy';
    case POWER_ONLY = 'power only';
    case REEFER = 'reefer';
    case REMOVABLE_GOOSENECK = 'removable gooseneck';
    case SLIDE = 'slide';
    case STEP_DECK = 'step deck';
    case TANKER = 'tanker';
}
