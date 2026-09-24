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
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('products', 'currency')) {
                $table->char('currency', 3)->default('USD')->after('sale_price');
            }

            if (! Schema::hasColumn('products', 'tax_included')) {
                $table->boolean('tax_included')->default(false)->after('currency');
            }

            if (! Schema::hasColumn('products', 'status')) {
                $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->after('tax_included');
            }

            if (! Schema::hasColumn('products', 'warranty_days')) {
                $table->unsignedInteger('warranty_days')->nullable()->after('status');
            }

            if (! Schema::hasColumn('products', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('warranty_days');
            }

            if (! Schema::hasColumn('products', 'part_number')) {
                $table->string('part_number', 120)->nullable()->after('supplier_id');
            }

            if (! Schema::hasColumn('products', 'compatible_models')) {
                $table->json('compatible_models')->nullable()->after('part_number');
            }

            if (! Schema::hasColumn('products', 'min_stock')) {
                $table->unsignedInteger('min_stock')->default(0)->after('compatible_models');
            }
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            if (! Schema::hasColumn('product_stocks', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('product_id')->constrained('branches')->nullOnDelete();
            }

            if (! Schema::hasColumn('product_stocks', 'last_updated')) {
                $table->timestamp('last_updated')->nullable()->useCurrent()->after('minimum_quantity');
            }
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            try {
                $table->dropUnique(['product_id']);
            } catch (\Throwable $e) {
                // Ignore if no unique constraint exists.
            }

            $table->unique(['product_id', 'branch_id'], 'product_stocks_product_branch_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            try {
                $table->dropUnique('product_stocks_product_branch_unique');
            } catch (\Throwable $e) {
                // Ignore if index does not exist.
            }

            if (Schema::hasColumn('product_stocks', 'branch_id')) {
                $table->dropConstrainedForeignId('branch_id');
            }

            if (Schema::hasColumn('product_stocks', 'last_updated')) {
                $table->dropColumn('last_updated');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $columns = ['sale_price', 'currency', 'tax_included', 'status', 'warranty_days', 'supplier_id', 'part_number', 'compatible_models', 'min_stock'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
