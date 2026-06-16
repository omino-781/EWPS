<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class EventRepository implements EventRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): Paginator
    {
        $query = Event::query()
            ->select([
                'events.id',
                'events.organizer_id',
                'events.category_id',
                'events.venue_id',
                'events.name',
                'events.description',
                'events.start_date',
                'events.end_date',
                'events.budget',
                'events.status',
                'event_categories.name as category_name',
                'venues.name as venue_name',
                'organizers.name as organizer_name',
            ])
            ->leftJoin('event_categories', 'event_categories.id', '=', 'events.category_id')
            ->leftJoin('venues', 'venues.id', '=', 'events.venue_id')
            ->leftJoin('users as organizers', 'organizers.id', '=', 'events.organizer_id');

        if (! empty($filters['published_only'])) {
            $query->where('events.status', 'published');
        } elseif (! empty($filters['status'])) {
            $query->where('events.status', $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('events.category_id', $filters['category_id']);
        }

        if (! empty($filters['organizer_id'])) {
            $query->where('events.organizer_id', $filters['organizer_id']);
        }

        if (! empty($filters['vendor_email'])) {
            $query->whereHas('vendors', fn ($q) => $q->where('vendors.email', $filters['vendor_email']));
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('events.name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('events.description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->latest('events.start_date')->simplePaginate($perPage);
    }

    public function find(int $id): ?Event
    {
        return Event::with(['category', 'venue', 'organizer', 'registrations', 'tasks', 'budgetRecord'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?Event
    {
        return Event::with(['category', 'venue', 'organizer'])->where('slug', $slug)->first();
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->fresh();
    }

    public function delete(Event $event): bool
    {
        return (bool) $event->delete();
    }

    public function upcoming(): Collection
    {
        return Event::where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(10)
            ->get();
    }

    public function calendarEvents(string $start, string $end): Collection
    {
        return Event::whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->get();
    }
}
