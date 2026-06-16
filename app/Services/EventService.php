<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventService
{
    public function __construct(
        protected EventRepositoryInterface $events,
        protected VenueBookingService $venueBookings,
        protected ActivityLogService $activityLog,
    ) {}

    public function create(array $data): Event
    {
        $data['slug'] = $this->uniqueSlug($data['name']);

        $event = $this->events->create($data);

        Budget::create([
            'event_id' => $event->id,
            'total_amount' => $data['budget'] ?? 0,
            'status' => 'draft',
        ]);

        if (! empty($data['venue_id'])) {
            $this->venueBookings->book(
                $data['venue_id'],
                $event->id,
                Carbon::parse($data['start_date']),
                Carbon::parse($data['end_date'])
            );
        }

        $this->activityLog->log('event.created', $event);

        return $event->load(['category', 'venue', 'organizer', 'budgetRecord']);
    }

    public function update(Event $event, array $data): Event
    {
        if (isset($data['name']) && $data['name'] !== $event->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $event->id);
        }

        if (array_key_exists('venue_id', $data)) {
            if (! empty($data['venue_id'])) {
                $this->venueBookings->book(
                    $data['venue_id'],
                    $event->id,
                    Carbon::parse($data['start_date'] ?? $event->start_date),
                    Carbon::parse($data['end_date'] ?? $event->end_date)
                );
            } else {
                $event->venueBooking()->delete();
            }
        }

        $updated = $this->events->update($event, $data);

        if (isset($data['budget'])) {
            $event->budgetRecord()->updateOrCreate(
                ['event_id' => $event->id],
                ['total_amount' => $data['budget'] ?? 0]
            );
        }

        $this->activityLog->log('event.updated', $updated);

        return $updated;
    }

    public function delete(Event $event): bool
    {
        $this->activityLog->log('event.deleted', $event);

        return $this->events->delete($event);
    }

    protected function uniqueSlug(string $name, ?int $exceptId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (Event::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
