<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'label' => 'Application Name',
                'value' => 'Event Planning Management System',
                'type' => 'text',
                'setting_group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'institution_name',
                'label' => 'Institution Name',
                'value' => 'Multimedia University of Kenya',
                'type' => 'text',
                'setting_group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'support_email',
                'label' => 'Support Email',
                'value' => 'support@epms.local',
                'type' => 'email',
                'setting_group' => 'communication',
                'is_public' => true,
            ],
            [
                'key' => 'support_phone',
                'label' => 'Support Phone',
                'value' => '+254700000000',
                'type' => 'text',
                'setting_group' => 'communication',
                'is_public' => true,
            ],
            [
                'key' => 'currency_code',
                'label' => 'Currency Code',
                'value' => 'KES',
                'type' => 'text',
                'setting_group' => 'finance',
                'is_public' => true,
            ],
            [
                'key' => 'registration_cutoff_hours',
                'label' => 'Registration Cutoff Hours',
                'value' => '0',
                'type' => 'number',
                'setting_group' => 'events',
                'is_public' => false,
            ],
            [
                'key' => 'enable_vendor_notifications',
                'label' => 'Enable Vendor Notifications',
                'value' => '1',
                'type' => 'boolean',
                'setting_group' => 'communication',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
