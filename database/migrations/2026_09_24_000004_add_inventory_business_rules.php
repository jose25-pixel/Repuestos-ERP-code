<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->decimal('porcentaje_iva', 5, 2)->default(13.00)->after('currency');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('precio_venta_sugerido', 12, 2)->default(0)->after('price');
            $table->decimal('precio_costo', 12, 2)->default(0)->after('cost');
            $table->boolean('exento_iva')->default(false)->after('tax_included');
            $table->boolean('activo')->default(true)->after('is_active');
        });

        DB::statement('UPDATE products SET precio_venta_sugerido = COALESCE(price, 0), precio_costo = COALESCE(cost, 0), activo = COALESCE(is_active, true)');

        Schema::create('inventarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('branches')->cascadeOnDelete();
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_minima')->default(0);
            $table->decimal('precio_venta_local', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'sucursal_id']);
            $table->index(['sucursal_id', 'cantidad_disponible']);
        });

        DB::statement(''
            . 'INSERT INTO inventarios (producto_id, sucursal_id, cantidad_disponible, cantidad_minima, created_at, updated_at) '
            . 'SELECT product_id, branch_id, quantity, minimum_quantity, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP '
            . 'FROM product_stocks '
            . 'WHERE branch_id IS NOT NULL '
            . 'ON CONFLICT (producto_id, sucursal_id) DO NOTHING'
        );

        Schema::create('kardex_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('sucursal_id')->constrained('branches')->restrictOnDelete();
            $table->string('tipo', 30);
            $table->unsignedInteger('cantidad');
            $table->string('referencia')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['producto_id', 'sucursal_id', 'created_at']);
            $table->index(['tipo', 'created_at']);
        });

        DB::statement("ALTER TABLE kardex_movimientos ADD CONSTRAINT kardex_movimientos_tipo_check CHECK (tipo IN ('entrada', 'salida', 'ajuste', 'traslado_salida', 'traslado_entrada', 'carga_inicial'))");
        DB::statement('ALTER TABLE inventarios ADD CONSTRAINT inventarios_cantidad_disponible_check CHECK (cantidad_disponible >= 0)');
        DB::statement('ALTER TABLE inventarios ADD CONSTRAINT inventarios_cantidad_minima_check CHECK (cantidad_minima >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex_movimientos');
        Schema::dropIfExists('inventarios');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['precio_venta_sugerido', 'precio_costo', 'exento_iva', 'activo']);
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn('porcentaje_iva');
        });
    }
};
