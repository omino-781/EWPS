<?php

namespace App\Repositories;

use App\Models\Venue;
use App\Repositories\Contracts\VenueRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VenueRepository implements VenueRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Venue::orderBy('name')->paginate($perPage);
    }

    public function find(int $id): ?Venue
    {
        return Venue::find($id);
    }

    public function findOrFail(int $id): Venue
    {
        return Venue::findOrFail($id);
    }

    public function create(array $data): Venue
    {
        return Venue::create($data);
    }

    public function update(Venue $venue, array $data): Venue
    {
        $venue->update($data);

        return $venue->fresh();
    }

    public function delete(Venue $venue): bool
    {
        return (bool) $venue->delete();
    }
}
