<?php

namespace App\Repositories\Contracts;

use App\Models\Venue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VenueRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Venue;

    public function create(array $data): Venue;

    public function update(Venue $venue, array $data): Venue;

    public function delete(Venue $venue): bool;
}
