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

    /**
     * Finds the matching Enum case from the capitalized string stored in the database.
     */
    public static function fromDLane(string $dbLane): ?self
    {
        // Optional: Perform a case-insensitive match for robustness
        $normalizedDbTrailer = trim($dbLane);

        foreach (self::cases() as $case) {
            if ($case->value === $normalizedDbTrailer) {
                return $case;
            }
        }
        
        // Handle cases where the DB string doesn't match a defined Enum case
        return null;
    } 
    
    /**
     * Gets the hyphenated, lower-case icon name (e.g., 'step-deck').
     */
    public function iconName(): string
    {
        // Convert the Enum's backing value ('Step Deck') to 'step-deck'
        return strtolower(str_replace(' ', '-', $this->value));
    }    
}
