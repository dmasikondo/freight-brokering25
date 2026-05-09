<?php

namespace App\Enums;

enum PlannedAction: string
{
    case ToPhone          = 'to_phone';
    case ToEmail          = 'to_email';
    case ToVisit          = 'to_visit';
    case ToRegister       = 'to_register';
    case ToWhatsApp       = 'to_whatsapp';
    case ToSendProposal   = 'to_send_proposal';
    case ToFollowUp       = 'to_follow_up';
    case Custom           = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ToPhone        => 'To Phone',
            self::ToEmail        => 'To Email',
            self::ToVisit        => 'To Visit',
            self::ToRegister     => 'To Register',
            self::ToWhatsApp     => 'To WhatsApp',
            self::ToSendProposal => 'To Send Proposal',
            self::ToFollowUp     => 'To Follow Up',
            self::Custom         => 'Custom…',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ToPhone        => 'phone',
            self::ToEmail        => 'envelope',
            self::ToVisit        => 'map-pin',
            self::ToRegister     => 'clipboard-document-check',
            self::ToWhatsApp     => 'chat-bubble-left-ellipsis',
            self::ToSendProposal => 'document-text',
            self::ToFollowUp     => 'arrow-path',
            self::Custom         => 'pencil',
        };
    }

    /** Whether this value requires the user to type a custom description. */
    public function requiresCustomText(): bool
    {
        return $this === self::Custom;
    }

    /**
     * Display label for a given entry — shows custom text if applicable.
     */
    public function displayLabel(?string $customText = null): string
    {
        if ($this === self::Custom && $customText) {
            return $customText;
        }

        return $this->label();
    }
}