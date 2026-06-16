<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'administrator', 'description' => 'Full system access'],
            ['name' => 'Event Organizer', 'slug' => 'organizer', 'description' => 'Manages events and operations'],
            ['name' => 'Participant', 'slug' => 'participant', 'description' => 'Registers and attends events'],
            ['name' => 'Vendor', 'slug' => 'vendor', 'description' => 'Views assigned events and payment notifications'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'module' => 'users'],
            ['name' => 'Manage Venues', 'slug' => 'venues.manage', 'module' => 'venues'],
            ['name' => 'Manage Events', 'slug' => 'events.manage', 'module' => 'events'],
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Manage Payments', 'slug' => 'payments.manage', 'module' => 'payments'],
            ['name' => 'Manage Vendors', 'slug' => 'vendors.manage', 'module' => 'vendors'],
            ['name' => 'View Assigned Events', 'slug' => 'events.view_assigned', 'module' => 'events'],
            ['name' => 'View Own Payments', 'slug' => 'payments.view_own', 'module' => 'payments'],
            ['name' => 'Manage System Settings', 'slug' => 'settings.manage', 'module' => 'settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        $admin = Role::where('slug', 'administrator')->first();
        $organizer = Role::where('slug', 'organizer')->first();
        $vendor = Role::where('slug', 'vendor')->first();

        $admin?->permissions()->sync(Permission::pluck('id'));
        $organizer?->permissions()->sync(
            Permission::whereIn('slug', ['events.manage', 'venues.manage', 'vendors.manage', 'reports.view', 'payments.manage'])->pluck('id')
        );
        $vendor?->permissions()->sync(
            Permission::whereIn('slug', ['events.view_assigned', 'payments.view_own'])->pluck('id')
        );
    }
}
