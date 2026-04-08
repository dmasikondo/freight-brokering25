<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\WorksheetEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorksheetReminders
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Only run for logged-in users on standard page loads
        if (auth()->check() && $request->isMethod('GET') && !$request->ajax()) {
            $userId = auth()->id();

            // 2. Find the oldest overdue reminder for this user
            $reminder = WorksheetEntry::where('notified_as_reminder', false)
                ->whereNotNull('reminder_at')
                ->where('reminder_at', '<=', now())
                ->whereHas('header', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhereHas('sharedWith', fn($q) => $q->where('user_id', $userId));
                })
                ->first();

            if ($reminder) {
                // 3. Mark as notified so it doesn't fire again
                $reminder->update(['notified_as_reminder' => true]);

                // 4. Flash to session to be picked up by your layout's toast/alert
                session()->flash('worksheet_reminder', [
                    'partner' => $reminder->partner_name,
                    'id' => $reminder->header_id,
                ]);
            }
        }

        return $next($request);
    }
}