<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notifications->markAllRead(auth()->user());

        return back()->with('success', 'All notifications marked as read.');
    }
}
