<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Goes straight to the delivery agent, on top of the delivery
            // fee — kept as its own column (not folded silently into
            // subtotal/delivery_fee) so it's easy to show separately on the
            // receipt and to total up an agent's tip earnings.
            $table->decimal('tip_amount', 8, 2)->default(0)->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tip_amount');
        });
    }
};
