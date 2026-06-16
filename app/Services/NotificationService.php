<?php

namespace App\Services;

use App\Mail\EpmsNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(protected SmsService $sms) {}

    public function sendToUser(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        array $channels = ['in_app', 'email']
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'channel' => 'in_app',
            'data' => $data,
            'sent_at' => now(),
        ]);

        if (in_array('email', $channels, true) && $user->email) {
            Mail::to($user)->queue(new EpmsNotificationMail($title, $message));
            $notification->update(['channel' => 'email']);
        }

        if (in_array('sms', $channels, true) && $user->phone) {
            $this->sms->send($user->phone, "{$title}: {$message}");
        }

        return $notification;
    }

    public function markAllRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
