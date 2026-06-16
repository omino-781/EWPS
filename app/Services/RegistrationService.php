<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationService
{
    public function __construct(
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog,
    ) {}

    public function register(Event $event, User $user): EventRegistration
    {
        if ($event->status !== 'published') {
            throw new \RuntimeException('Event is not open for registration.');
        }

        if ($event->max_attendees && $event->registrations()->count() >= $event->max_attendees) {
            throw new \RuntimeException('Event has reached maximum capacity.');
        }

        if ($event->registrations()->where('user_id', $user->id)->exists()) {
            throw new \RuntimeException('You are already registered for this event.');
        }

        Attendee::firstOrCreate(['user_id' => $user->id]);

        $code = strtoupper(Str::random(12));
        $qrPath = 'qrcodes/'.$code.'.svg';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><text x="10" y="100" font-size="12">'.$code.'</text></svg>';
        Storage::disk('public')->put($qrPath, $svg);

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_code' => $code,
            'qr_code_path' => $qrPath,
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        $this->notifications->sendToUser(
            $user,
            'registration_confirmation',
            'Registration Confirmed',
            "You have successfully registered for {$event->name}. Code: {$code}",
            ['event_id' => $event->id, 'registration_code' => $code]
        );

        $this->activityLog->log('registration.created', $registration);

        return $registration;
    }

    public function checkIn(EventRegistration $registration): EventRegistration
    {
        $registration->update([
            'status' => 'attended',
            'checked_in_at' => now(),
        ]);

        $this->activityLog->log('registration.checked_in', $registration);

        return $registration;
    }
}
