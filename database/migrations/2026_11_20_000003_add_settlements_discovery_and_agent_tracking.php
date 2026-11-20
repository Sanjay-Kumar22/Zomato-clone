<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $restaurantIndexes = Schema::hasTable('restaurants') ? Schema::getIndexes('restaurants') : [];
        $hasFeaturedIndex = collect($restaurantIndexes)->contains(
            fn (array $index): bool => $index['columns'] === ['is_featured']
        );
        if (!Schema::hasColumn('restaurants', 'is_featured')) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false);
            });
        }
        if (!$hasFeaturedIndex) {
            Schema::table('restaurants', function (Blueprint $table) {
                $table->index('is_featured');
            });
        }

        $missingOrderColumns = array_filter(
            ['cod_collected_amount', 'cod_collected_at'],
            fn (string $column): bool => !Schema::hasColumn('orders', $column)
        );
        if ($missingOrderColumns !== []) {
            Schema::table('orders', function (Blueprint $table) use ($missingOrderColumns) {
                if (in_array('cod_collected_amount', $missingOrderColumns, true)) {
                    $table->decimal('cod_collected_amount', 10, 2)->nullable();
                }
                if (in_array('cod_collected_at', $missingOrderColumns, true)) {
                    $table->timestamp('cod_collected_at')->nullable();
                }
            });
        }

        if (!Schema::hasTable('restaurant_settlements')) {
            Schema::create('restaurant_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('gross_amount', 12, 2);
                $table->decimal('commission_amount', 12, 2);
                $table->decimal('payout_amount', 12, 2);
                $table->string('status', 20)->default('pending');
                $table->string('payout_reference')->nullable();
                $table->text('review_note')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
                $table->index(['restaurant_id', 'status']);
            });
        }

        if (!Schema::hasTable('restaurant_settlement_orders')) {
            Schema::create('restaurant_settlement_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('restaurant_settlement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->decimal('gross_amount', 10, 2);
                $table->decimal('commission_amount', 10, 2);
                $table->decimal('payout_amount', 10, 2);
                $table->timestamps();
            });
        }
        $settlementOrderIndexes = Schema::getIndexes('restaurant_settlement_orders');
        $hasSettlementOrderUnique = collect($settlementOrderIndexes)->contains(
            fn (array $index): bool => $index['unique']
                && $index['columns'] === ['restaurant_settlement_id', 'order_id']
        );
        if (!$hasSettlementOrderUnique) {
            Schema::table('restaurant_settlement_orders', function (Blueprint $table) {
                $table->unique(
                    ['restaurant_settlement_id', 'order_id'],
                    'settlement_orders_settlement_order_unique'
                );
            });
        }

        if (!Schema::hasTable('delivery_agent_location_history')) {
            Schema::create('delivery_agent_location_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->decimal('lat', 10, 7);
                $table->decimal('lng', 10, 7);
                $table->timestamp('recorded_at');
                $table->index(['user_id', 'recorded_at']);
                $table->index(['order_id', 'recorded_at']);
            });
        }

        if (!Schema::hasTable('customer_recent_searches')) {
            Schema::create('customer_recent_searches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('term', 120);
                $table->timestamp('searched_at');
                $table->index(['user_id', 'searched_at']);
            });
        }

        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('image');
                $table->string('url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['is_active', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
        Schema::dropIfExists('customer_recent_searches');
        Schema::dropIfExists('delivery_agent_location_history');
        Schema::dropIfExists('restaurant_settlement_orders');
        Schema::dropIfExists('restaurant_settlements');

        $orderColumns = array_filter(
            ['cod_collected_amount', 'cod_collected_at'],
            fn (string $column): bool => Schema::hasColumn('orders', $column)
        );
        if ($orderColumns !== []) {
            Schema::table('orders', function (Blueprint $table) use ($orderColumns) {
                $table->dropColumn($orderColumns);
            });
        }

        if (Schema::hasColumn('restaurants', 'is_featured')) {
            $restaurantIndexes = Schema::getIndexes('restaurants');
            if (collect($restaurantIndexes)->contains(
                fn (array $index): bool => $index['columns'] === ['is_featured']
            )) {
                Schema::table('restaurants', function (Blueprint $table) {
                    $table->dropIndex(['is_featured']);
                });
            }
            Schema::table('restaurants', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }
    }
};
