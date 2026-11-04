<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->enum('zone_type', ['metro', 'remote', 'blocked'])->default('metro')->after('pincode');
            $table->string('estimated_time')->nullable()->after('delivery_fee'); // e.g. "30 min"
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['zone_type', 'estimated_time']);
        });
    }
};
