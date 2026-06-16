<?php

return [
    'roles' => [
        'administrator' => 'administrator',
        'organizer' => 'organizer',
        'participant' => 'participant',
        'vendor' => 'vendor',
    ],

    'auth_roles' => [
        'administrator' => 'Admin',
        'organizer' => 'Organiser',
        'participant' => 'Attendee / Participant',
        'vendor' => 'Vendor',
    ],

    'event_statuses' => [
        'draft', 'published', 'ongoing', 'completed', 'cancelled',
    ],

    'task_statuses' => ['pending', 'in_progress', 'completed'],

    'payment_statuses' => ['pending', 'partial', 'paid', 'refunded', 'failed'],

    'payment_methods' => ['cash', 'bank_transfer', 'card', 'mpesa', 'airtel_money'],

    'report_formats' => ['pdf', 'excel', 'csv'],

    'report_types' => [
        'event', 'attendance', 'financial', 'venue_utilization', 'vendor', 'task_progress',
    ],
];
