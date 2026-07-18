<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): View
    {
        $notifications = Auth::user()->notifications()->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
