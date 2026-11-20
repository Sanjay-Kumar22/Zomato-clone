<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('special_instructions')->nullable();
            $table->text('cooking_notes')->nullable();
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        DB::table('orders')
            ->select(['id', 'status', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($orders): void {
                DB::table('order_status_histories')->insert(
                    $orders->map(fn ($order) => [
                        'order_id' => $order->id,
                        'changed_by' => null,
                        'from_status' => null,
                        'to_status' => $order->status,
                        'created_at' => $order->updated_at,
                        'updated_at' => $order->updated_at,
                    ])->all()
                );
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['special_instructions', 'cooking_notes']);
        });
    }
};
