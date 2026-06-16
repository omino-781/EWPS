<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['status', 'start_date'], 'events_status_start_date_listing_idx');
            $table->index(['organizer_id', 'start_date'], 'events_organizer_start_date_idx');
            $table->index(['category_id', 'status', 'start_date'], 'events_category_status_start_date_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->index('email', 'vendors_email_idx');
        });

        Schema::table('event_vendors', function (Blueprint $table) {
            $table->index(['vendor_id', 'status', 'event_id'], 'event_vendors_vendor_status_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('event_vendors', function (Blueprint $table) {
            $table->dropIndex('event_vendors_vendor_status_event_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_email_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_category_status_start_date_idx');
            $table->dropIndex('events_organizer_start_date_idx');
            $table->dropIndex('events_status_start_date_listing_idx');
        });
    }
};
