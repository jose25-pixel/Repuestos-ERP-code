<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Inventario;
use App\Models\KardexMovimiento;
use App\Models\Product;
use App\Services\InventarioService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    public function test_entry_and_exit_are_recorded_in_kardex(): void
    {
        [$product, $branch] = $this->productAndBranch();
        $service = app(InventarioService::class);

        $service->registrarEntrada($product, $branch, 10, 'FAC-001');
        $service->registrarSalida($product, $branch, 3, 'POS-001');

        $this->assertSame(7, Inventario::query()
            ->where('producto_id', $product->id)
            ->where('sucursal_id', $branch->id)
            ->value('cantidad_disponible'));
        $this->assertDatabaseHas('kardex_movimientos', [
            'producto_id' => $product->id,
            'sucursal_id' => $branch->id,
            'tipo' => KardexMovimiento::TIPO_ENTRADA,
            'cantidad' => 10,
        ]);
        $this->assertDatabaseHas('kardex_movimientos', [
            'producto_id' => $product->id,
            'sucursal_id' => $branch->id,
            'tipo' => KardexMovimiento::TIPO_SALIDA,
            'cantidad' => 3,
        ]);
    }

    public function test_exit_cannot_make_stock_negative(): void
    {
        [$product, $branch] = $this->productAndBranch();
        $service = app(InventarioService::class);
        $service->registrarEntrada($product, $branch, 2);

        $this->expectException(ValidationException::class);
        $service->registrarSalida($product, $branch, 3);
    }

    public function test_transfer_creates_both_kardex_movements(): void
    {
        [$product, $origin] = $this->productAndBranch();
        $destination = Branch::create([
            'company_id' => $origin->company_id,
            'name' => 'Destino '.uniqid(),
            'country' => 'SV',
            'city' => 'San Salvador',
            'is_active' => true,
        ]);
        $service = app(InventarioService::class);
        $service->registrarEntrada($product, $origin, 8);

        $service->registrarTraslado($product, $origin, $destination, 5);

        $this->assertSame(3, Inventario::query()->where('producto_id', $product->id)->where('sucursal_id', $origin->id)->value('cantidad_disponible'));
        $this->assertSame(5, Inventario::query()->where('producto_id', $product->id)->where('sucursal_id', $destination->id)->value('cantidad_disponible'));
        $this->assertDatabaseHas('kardex_movimientos', ['sucursal_id' => $origin->id, 'tipo' => KardexMovimiento::TIPO_TRASLADO_SALIDA, 'cantidad' => 5]);
        $this->assertDatabaseHas('kardex_movimientos', ['sucursal_id' => $destination->id, 'tipo' => KardexMovimiento::TIPO_TRASLADO_ENTRADA, 'cantidad' => 5]);
    }

    public function test_price_breakdown_uses_company_iva_and_exemption(): void
    {
        [$product, $branch] = $this->productAndBranch(13.00, 113.00, false);

        $breakdown = $product->precioConDesglose($branch->id);

        $this->assertSame(113.0, $breakdown['precio_con_iva']);
        $this->assertSame(100.0, $breakdown['precio_sin_iva']);
        $this->assertSame(13.0, $breakdown['iva']);
    }

    private function productAndBranch(float $iva = 13.00, float $price = 100.00, bool $exempt = false): array
    {
        $company = Company::create([
            'name' => 'Empresa inventario '.uniqid(),
            'tax_id' => 'J-'.uniqid('INV-', true),
            'country' => 'SV',
            'currency' => 'USD',
            'porcentaje_iva' => $iva,
            'plan' => 'basic',
            'is_active' => true,
        ]);
        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Repuestos '.uniqid(),
        ]);
        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Principal '.uniqid(),
            'country' => 'SV',
            'city' => 'San Salvador',
            'is_active' => true,
        ]);
        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'SKU-'.uniqid(),
            'name' => 'Producto inventario',
            'cost' => 50,
            'price' => $price,
            'precio_costo' => 50,
            'precio_venta_sugerido' => $price,
            'exento_iva' => $exempt,
            'is_active' => true,
            'activo' => true,
        ]);

        return [$product, $branch];
    }
}
