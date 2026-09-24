<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('sucursal_id')->nullable()->after('company_id')->constrained('branches')->nullOnDelete();
            $table->string('canal', 20)->default('online')->after('status');
            $table->index(['company_id', 'sucursal_id', 'canal']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['sucursal_id']);
            $table->dropIndex(['company_id', 'sucursal_id', 'canal']);
            $table->dropColumn(['sucursal_id', 'canal']);
        });
    }
};
