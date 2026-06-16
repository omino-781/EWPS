<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'city', 'capacity',
        'hourly_rate', 'amenities', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(VenueBooking::class);
    }
}
