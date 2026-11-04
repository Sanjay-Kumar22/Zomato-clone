<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('agent_lat', 10, 7)->nullable()->after('delivery_agent_id');
            $table->decimal('agent_lng', 10, 7)->nullable()->after('agent_lat');
            $table->timestamp('agent_location_updated_at')->nullable()->after('agent_lng');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['agent_lat', 'agent_lng', 'agent_location_updated_at']);
        });
    }
};
