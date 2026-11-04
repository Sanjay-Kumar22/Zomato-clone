<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Orders keep a hard foreign key to their delivery address (so
            // order history always shows exactly where it was delivered),
            // which blocks a real DELETE once an address has been used in
            // any order. Soft-deleting instead lets the customer remove it
            // from their active list while past orders keep working fine.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
