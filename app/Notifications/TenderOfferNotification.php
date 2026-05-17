<?php

namespace App\Notifications;

use App\Models\TenderOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenderOfferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Events dispatched to STAFF when a bidder acts:
     *   offer_submitted | offer_revised | offer_withdrawn
     *
     * Events dispatched to the BIDDER when staff acts:
     *   offer_shortlisted | offer_rejected | offer_awarded
     */
    public function __construct(
        public readonly TenderOffer $offer,
        public readonly string      $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // ---------------------------------------------------------------
    // Database notification
    // ---------------------------------------------------------------

    public function toDatabase(object $notifiable): array
    {
        $offer      = $this->offer;
        $tenderable = $offer->tenderable;
        $bidder     = $offer->bidder;

        $tenderableLabel = $tenderable?->reference ?? $tenderable?->name ?? "#{$offer->tenderable_id}";
        $bidderName      = $bidder?->organisation ?? $bidder?->contact_person ?? 'A bidder';
        $amount          = 'US$' . number_format((float) $offer->amount, 2);

        [$title, $body, $url] = match ($this->event) {

            // ── Staff notifications (bidder acted) ──────────────────
            'offer_submitted' => [
                'New Offer Submitted',
                "{$bidderName} submitted an offer of {$amount} on tender {$tenderableLabel}.",
                $this->tenderableUrl($tenderable),
            ],
            'offer_revised' => [
                'Offer Revised',
                "{$bidderName} revised their offer to {$amount} on tender {$tenderableLabel}.",
                $this->tenderableUrl($tenderable),
            ],
            'offer_withdrawn' => [
                'Offer Withdrawn',
                "{$bidderName} withdrew their offer on tender {$tenderableLabel}.",
                $this->tenderableUrl($tenderable),
            ],

            // ── Bidder notifications (staff acted) ──────────────────
            'offer_shortlisted' => [
                'Your Offer Has Been Shortlisted',
                "Your offer of {$amount} on tender {$tenderableLabel} has been shortlisted.",
                $this->tenderableUrl($tenderable),
            ],
            'offer_rejected' => [
                'Your Offer Was Not Successful',
                "Your offer of {$amount} on tender {$tenderableLabel} was not successful."
                    . ($offer->rejection_reason ? ' Reason: ' . $offer->rejection_reason : ''),
                $this->tenderableUrl($tenderable),
            ],
            'offer_awarded' => [
                'Congratulations — Tender Awarded to You',
                "Your offer of {$amount} on tender {$tenderableLabel} has been awarded. Please await further instructions.",
                $this->tenderableUrl($tenderable),
            ],

            'award_revoked' => [
                'Award Revoked',
                "The award previously granted to you for tender {$tenderableLabel} ({$amount}) has been revoked. The tender has been re-opened.",
                $this->tenderableUrl($tenderable),
            ],

            'offer_nominated' => [
                'You Have Been Nominated for a Tender',
                "You have been nominated by our team for tender {$tenderableLabel}. An offer of {$amount} has been created on your behalf.",
                $this->tenderableUrl($tenderable),
            ],

            default => ['Tender Update', 'There has been an update on a tender offer.', '/'],
        };

        return [
            'event'          => $this->event,
            'offer_id'       => $offer->id,
            'tenderable_type' => $offer->tenderable_type,
            'tenderable_id'  => $offer->tenderable_id,
            'title'          => $title,
            'body'           => $body,
            'url'            => $url,
        ];
    }

    // ---------------------------------------------------------------
    // Helper — resolve the correct named route for each tenderable
    // ---------------------------------------------------------------

    private function tenderableUrl(mixed $tenderable): string
    {
        if (!$tenderable) return '/';

        return match (get_class($tenderable)) {
            \App\Models\Freight::class => route('freights.show', $tenderable->uuid),
            \App\Models\Lane::class    => route('lanes.show',    $tenderable->uuid),
            default                    => '/',
        };
    }
}
