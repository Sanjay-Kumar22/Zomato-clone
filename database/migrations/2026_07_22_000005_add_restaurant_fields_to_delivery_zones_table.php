<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->after('id')
                ->constrained()->onDelete('cascade');
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('pincode');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('restaurant_id');
            $table->dropColumn('delivery_fee');
        });
    }
};
