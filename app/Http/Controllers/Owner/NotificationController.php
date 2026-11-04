<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('owner.notifications', compact('notifications'));
    }

    public function markRead(string $notification)
    {
        $notif = auth()->user()->notifications()->where('id', $notification)->firstOrFail();
        $notif->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}
