<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id', 'name', 'email', 'password', 'phone', 'avatar', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function attendee(): HasOne
    {
        return $this->hasOne(Attendee::class);
    }

    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdministrator(): bool
    {
        return $this->role?->slug === 'administrator';
    }

    public function isOrganizer(): bool
    {
        return $this->role?->slug === 'organizer';
    }

    public function isParticipant(): bool
    {
        return $this->role?->slug === 'participant';
    }

    public function isVendor(): bool
    {
        return $this->role?->slug === 'vendor';
    }

    public function vendorProfile(): ?Vendor
    {
        return Vendor::where('email', $this->email)->first();
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        return $this->role?->hasPermission($slug) ?? false;
    }
}
