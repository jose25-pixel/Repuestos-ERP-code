<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'country')) {
                $table->string('country')->nullable();
            }

            if (! Schema::hasColumn('customers', 'department')) {
                $table->string('department')->nullable();
            }

            if (! Schema::hasColumn('customers', 'municipality')) {
                $table->string('municipality')->nullable();
            }

            if (! Schema::hasColumn('customers', 'address')) {
                $table->string('address')->nullable();
            }

            if (! Schema::hasColumn('customers', 'address_reference')) {
                $table->string('address_reference')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('customers', 'country') ? 'country' : null,
                Schema::hasColumn('customers', 'department') ? 'department' : null,
                Schema::hasColumn('customers', 'municipality') ? 'municipality' : null,
                Schema::hasColumn('customers', 'address') ? 'address' : null,
                Schema::hasColumn('customers', 'address_reference') ? 'address_reference' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};