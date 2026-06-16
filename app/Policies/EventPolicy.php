<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Event $event): bool
    {
        return $user->isAdministrator()
            || $event->organizer_id === $user->id
            || ($user->isVendor() && $event->vendors()->where('vendors.email', $user->email)->exists())
            || $event->status === 'published';
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator() || $user->isOrganizer();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->isAdministrator() || $event->organizer_id === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->isAdministrator() || $event->organizer_id === $user->id;
    }

    public function register(User $user, Event $event): bool
    {
        return $user->isParticipant() && $event->status === 'published';
    }
}
