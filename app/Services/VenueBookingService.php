<?php

namespace App\Services;

use App\Models\VenueBooking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VenueBookingService
{
    /**
     * Detect overlapping bookings for a venue in the given time range.
     */
    public function hasConflict(int $venueId, Carbon $start, Carbon $end, ?int $excludeEventId = null): bool
    {
        $query = VenueBooking::where('venue_id', $venueId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            });

        if ($excludeEventId) {
            $query->where('event_id', '!=', $excludeEventId);
        }

        return $query->exists();
    }

    public function book(int $venueId, int $eventId, Carbon $start, Carbon $end, ?string $notes = null): VenueBooking
    {
        if ($this->hasConflict($venueId, $start, $end, $eventId)) {
            throw new \RuntimeException('Venue is already booked for the selected time period.');
        }

        return VenueBooking::updateOrCreate(
            ['event_id' => $eventId],
            [
                'venue_id' => $venueId,
                'start_time' => $start,
                'end_time' => $end,
                'status' => 'confirmed',
                'notes' => $notes,
            ]
        );
    }

    public function utilizationReport(): Collection
    {
        return VenueBooking::with('venue', 'event')
            ->where('status', 'confirmed')
            ->get()
            ->groupBy('venue_id');
    }
}
