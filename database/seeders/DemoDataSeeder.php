<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\Venue;
use App\Services\EventService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'administrator')->first();
        $organizerRole = Role::where('slug', 'organizer')->first();
        $participantRole = Role::where('slug', 'participant')->first();
        $vendorRole = Role::where('slug', 'vendor')->first();

        User::firstOrCreate(['email' => 'admin@epms.local'], [
            'role_id' => $adminRole->id,
            'name' => 'System Administrator',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $organizer = User::firstOrCreate(['email' => 'organizer@epms.local'], [
            'role_id' => $organizerRole->id,
            'name' => 'Jane Organizer',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'participant@epms.local'], [
            'role_id' => $participantRole->id,
            'name' => 'John Participant',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'vendor@epms.local'], [
            'role_id' => $vendorRole->id,
            'name' => 'Vendor Partner',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $venue = Venue::firstOrCreate(['name' => 'Grand Hall'], [
            'description' => 'Main conference hall',
            'address' => '123 Campus Road',
            'city' => 'Nairobi',
            'capacity' => 500,
            'hourly_rate' => 15000,
            'is_active' => true,
        ]);

        $category = EventCategory::first();
        $vendorCategory = VendorCategory::where('name', 'Catering')->first() ?? VendorCategory::first();

        $vendor = Vendor::firstOrCreate(['email' => 'vendor@epms.local'], [
            'category_id' => $vendorCategory->id,
            'name' => 'Nairobi Catering Partners',
            'contact_person' => 'Vendor Partner',
            'phone' => '+254700000000',
            'address' => 'Nairobi',
            'rating' => 4.5,
            'is_active' => true,
        ]);

        if (! Event::where('slug', 'annual-tech-summit')->exists()) {
            $event = app(EventService::class)->create([
                'organizer_id' => $organizer->id,
                'category_id' => $category->id,
                'venue_id' => $venue->id,
                'name' => 'Annual Tech Summit',
                'description' => 'A flagship technology conference for innovators.',
                'start_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
                'end_date' => now()->addDays(31)->format('Y-m-d H:i:s'),
                'budget' => 500000,
                'status' => 'published',
                'max_attendees' => 300,
            ]);
        } else {
            $event = Event::where('slug', 'annual-tech-summit')->first();
        }

        $event?->vendors()->syncWithoutDetaching([
            $vendor->id => [
                'service_description' => 'Conference catering and refreshments',
                'contract_amount' => 75000,
                'status' => 'confirmed',
                'assigned_at' => now(),
            ],
        ]);
    }
}
