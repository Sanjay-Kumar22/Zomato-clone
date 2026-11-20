<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('restrict');
            $table->decimal('subtotal', 8, 2);
            $table->decimal('delivery_fee', 6, 2)->default(0);
            $table->decimal('total', 8, 2);
            $table->enum('status', ['pending','confirmed','preparing','out_for_delivery','delivered','cancelled'])
                ->default('pending');
            $table->string('payment_id')->nullable();
            $table->enum('payment_status', ['pending','paid','failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // A later settlement migration may have stopped partway through and
        // left this dependent table behind without recording its migration.
        Schema::dropIfExists('restaurant_settlement_orders');
        Schema::dropIfExists('restaurant_settlements');
        Schema::dropIfExists('orders');
    }
};
