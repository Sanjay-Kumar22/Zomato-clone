<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('pincode');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            // lat/lng already exist on restaurants, but ensure they're usable —
            // no-op if already present, kept here for clarity of intent.
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
