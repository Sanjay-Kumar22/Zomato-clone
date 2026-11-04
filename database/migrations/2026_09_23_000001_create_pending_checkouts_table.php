<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online-payment orders (Stripe / Razorpay) used to be written to the
 * `orders` table immediately, before the customer had actually paid — so an
 * abandoned/failed payment still left a permanent "order" behind, still
 * consumed the applied coupon, redeemed loyalty points, and even debited the
 * wallet, even though nothing was ever charged.
 *
 * This table holds the checkout as a draft instead. The real Order row (and
 * every side-effect: coupon usage, loyalty redemption, wallet debit, cart
 * clearing) is only created once the payment gateway confirms the payment
 * succeeded. Cash on Delivery orders don't need this — they're placed
 * immediately as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->enum('payment_method', ['stripe', 'razorpay']);
            $table->decimal('payable_amount', 10, 2); // amount actually charged via the gateway
            $table->json('payload'); // everything needed to create the Order once paid
            $table->string('gateway_reference')->nullable(); // stripe session id / razorpay order id
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_checkouts');
    }
};
