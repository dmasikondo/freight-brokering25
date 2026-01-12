<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\PasswordReset;

class MarkEmailAsVerifiedOnReset
{
    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        // $event->user is the user instance who just reset their password
        if (! $event->user->hasVerifiedEmail()) {
            $event->user->markEmailAsVerified();
        }
    }
}
