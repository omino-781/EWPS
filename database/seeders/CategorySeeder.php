<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use App\Models\VendorCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Conference', 'Workshop', 'Seminar', 'Gala', 'Sports'] as $name) {
            EventCategory::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => "{$name} events",
            ]);
        }

        foreach (['Catering', 'Photography', 'Decorations', 'Security', 'Entertainment'] as $name) {
            VendorCategory::firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'description' => "{$name} services",
            ]);
        }
    }
}
