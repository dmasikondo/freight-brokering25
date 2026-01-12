<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewClientRegistered extends Notification
{
    use Queueable;

    // Inject the newly registered user (the client)
    public function __construct(public User $client) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Get the role name (Shipper or Carrier)
        $roleName = ucfirst($this->client->roles->first()->name ?? 'Client');
        $orgName = $this->client->organisation ?? $this->client->contact_person;

        return (new MailMessage)
            ->subject("New {$roleName} Registered: {$orgName}")
            ->greeting("Hello, {$notifiable->contact_person}!")
            ->line("A new {$roleName} has registered within your assigned territory.")
            ->line("Organization: **{$orgName}**")
            ->action('View Profile', route('users.show', $this->client->slug))
            ->line('Please review their profile to initiate the onboarding process.');
    }

    public function toArray($notifiable): array
    {
        return [
            'client_id' => $this->client->slug,
            'client_name' => $this->client->organisation,
            'client_role' => $this->client->roles->first()->name ?? 'client',
            'message' => "New " . ($this->client->roles->first()->name ?? 'client') . " registered in your territory."
        ];
    }
}