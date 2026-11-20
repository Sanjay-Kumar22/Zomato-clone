<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 40)->default('monthly');
            $table->decimal('price_paid', 8, 2);
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['user_id', 'status', 'expires_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('membership_free_delivery')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('membership_free_delivery');
        });

        Schema::dropIfExists('customer_memberships');
    }
};
