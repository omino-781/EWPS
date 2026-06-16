<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): Paginator;

    public function find(int $id): ?Event;

    public function findBySlug(string $slug): ?Event;

    public function create(array $data): Event;

    public function update(Event $event, array $data): Event;

    public function delete(Event $event): bool;

    public function upcoming(): Collection;

    public function calendarEvents(string $start, string $end): Collection;
}
