<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountVerifiedNotification extends Notification
{
    use Queueable;

    protected $authorizerName;

    /**
     * Create a new notification instance.
     */
    public function __construct($authorizerName)
    {
        $this->authorizerName = $authorizerName;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Added 'database' to the delivery channels
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Account Verified - Transpartner Logistics Network')
            ->greeting("Hello, {$notifiable->contact_person}!")
            ->line("Your account has been successfully verified.")
            ->line('You now have full access to manage your fleet, lanes, and trade references.')
            ->action('View My Profile', url('/dashboard'))
            ->line('Thank you for being part of our network!');
    }

    /**
     * Get the array representation of the notification (Stored in Database).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Account Verified',
            'message' => "Your account was successfully verified",
          //  'authorizer' => $this->authorizerName,
            'action_url' => url('/dashboard'),
            'type' => 'success',
            'icon' => 'check-badge'
        ];
    }
}