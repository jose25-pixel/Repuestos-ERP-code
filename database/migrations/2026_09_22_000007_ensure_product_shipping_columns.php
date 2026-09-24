<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'weight_kg')) {
                $table->decimal('weight_kg', 8, 2)->default(0)->after('price');
            }

            if (! Schema::hasColumn('products', 'is_heavy')) {
                $table->boolean('is_heavy')->default(false)->after('weight_kg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('products', 'weight_kg') ? 'weight_kg' : null,
                Schema::hasColumn('products', 'is_heavy') ? 'is_heavy' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};