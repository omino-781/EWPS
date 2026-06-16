<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\VenueRepositoryInterface;
use App\Repositories\EventRepository;
use App\Repositories\VenueRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(VenueRepositoryInterface::class, VenueRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
    }
}
