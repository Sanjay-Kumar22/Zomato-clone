<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('minimum_order_value', 8, 2)->default(0);
            $table->unsignedSmallInteger('preparation_time_minutes')->default(30);
            $table->decimal('delivery_radius_km', 5, 2)->default(7);
            $table->decimal('average_cost_for_two', 8, 2)->nullable();
            $table->decimal('packaging_charge', 8, 2)->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('packaging_charge', 8, 2)->default(0);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('platform_fee', 8, 2)->default(0);
            $table->text('delivery_otp')->nullable();
            $table->timestamp('delivery_otp_verified_at')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_registration')->nullable();
            $table->string('kyc_document')->nullable();
            $table->string('kyc_status')->default('not_submitted');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('first_order_only')->default(false);
            $table->unsignedInteger('per_user_limit')->nullable();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('moderation_status', 20)->default('visible');
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id']);
            $table->index(['coupon_id', 'user_id']);
        });

        DB::table('orders')->whereNotNull('coupon_code')->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                $couponId = DB::table('coupons')->where('code', $order->coupon_code)->value('id');
                if ($couponId) {
                    DB::table('coupon_usages')->insertOrIgnore([
                        'coupon_id' => $couponId,
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->created_at,
                    ]);
                }
            }
        });

        Schema::create('review_helpful_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['review_id', 'user_id']);
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 30);
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 30)->default('pending');
            $table->string('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dispute_id')->nullable()->constrained('order_disputes')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method', 30);
            $table->string('status', 30)->default('pending');
            $table->string('gateway_refund_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });

        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 60);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->unique(['review_id', 'reported_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('payment_attempts');
        Schema::dropIfExists('review_helpful_votes');
        Schema::dropIfExists('coupon_usages');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['photo', 'moderation_status']);
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['first_order_only', 'per_user_limit']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['vehicle_type', 'vehicle_registration', 'kyc_document', 'kyc_status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'packaging_charge', 'tax_amount', 'platform_fee', 'delivery_otp',
                'delivery_otp_verified_at', 'estimated_delivery_at',
            ]);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn([
                'minimum_order_value', 'preparation_time_minutes', 'delivery_radius_km',
                'average_cost_for_two', 'packaging_charge',
            ]);
        });
    }
};
