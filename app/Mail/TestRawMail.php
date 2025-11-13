<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestRawMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Define the email's Subject line
        return new Envelope(
            subject: 'Laravel Component Email Test',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Define the email's content. Use 'htmlString' for raw HTML.
        $htmlBody = "
            <h1>Hello from Laravel Mail!</h1>
            <p>This email was sent securely using Laravel's Mail component and your configured driver.</p>
            <p>Date sent: " . date('Y-m-d H:i:s') . "</p>
        ";

        return new Content(
            htmlString: $htmlBody,
        );
    }
}