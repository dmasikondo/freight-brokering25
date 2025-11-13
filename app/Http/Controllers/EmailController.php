<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\TestRawMail;
use Illuminate\Support\Facades\Mail;
// Note: If you use a framework like Laravel, you may still need the Request class.

class EmailController extends Controller
{
    /**
     * Sends an email using Laravel's Mail component and Mailable class.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendLaravelEmail(Request $request)
    {
        $to = 'dmasikondo@gmail.com';
        $from = 'dmasikondo@gmail.com'; // Your configured sender address

        try {
            // 1. Set the sender (who the email is "from")
            // 2. Set the recipient (who the email is "to")
            // 3. Send the TestRawMail Mailable instance
            Mail::to($to)
                ->send(new TestRawMail());
            
            // Note: The 'from' address is typically configured in config/mail.php 
            // but can also be set within the Mailable if needed.

            return response()->json([
                'status' => 'success',
                'message' => 'Email successfully queued/sent to ' . $to
            ]);
        } catch (\Exception $e) {
            // Log the detailed error for debugging
            logger()->error('Email sending failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Email failed to send. Check logs and mail configuration.'
            ], 500);
        }
    }
}