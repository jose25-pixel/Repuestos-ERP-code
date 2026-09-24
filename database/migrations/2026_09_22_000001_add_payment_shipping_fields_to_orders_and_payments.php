<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('currency');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->string('shipping_zone')->nullable()->after('payment_status');
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('shipping_zone');
            $table->string('delivery_status')->default('pending')->after('shipping_cost');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider_reference')->nullable()->after('reference');
            $table->text('notes')->nullable()->after('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'shipping_zone', 'shipping_cost', 'delivery_status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['provider_reference', 'notes']);
        });
    }
};
