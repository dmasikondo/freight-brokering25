<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('status', 'Notification marked as read');
    }

    /**
     * Mark all unread notifications for the user as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function readAndView($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // 1. Mark as read
        $notification->markAsRead();

        // 2. Get the destination from the notification data
        $clientId = $notification->data['client_id'] ?? null;

        if ($clientId) {
            // 3. Go to the users.show route as requested
            return redirect()->route('users.show', $clientId);
        }

        return redirect()->route('notifications.index');
    }
}
