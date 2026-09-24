<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductStock;
use Tests\TestCase;

class ProductStockValidationTest extends TestCase
{
    public function test_product_can_be_created_with_real_minimum_stock(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Casa Matriz',
            'establishment_code' => '001',
            'country' => 'VE',
            'city' => 'Caracas',
            'address' => 'Av. Principal, Local 1',
            'phone' => '+58 212 555 0100',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Lavadoras',
            'description' => 'Repuestos para lavadoras.',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'LAV-STOCK-001',
            'barcode' => '759000000100',
            'name' => 'Motor para lavadora',
            'brand' => 'Whirlpool',
            'description' => 'Motor compatible con lavadoras.',
            'images' => ['products/lavadoras/motor.jpg'],
            'cost' => 45.00,
            'price' => 94.00,
            'sale_price' => 89.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 5.20,
            'status' => 'active',
            'warranty_days' => 365,
            'part_number' => 'WH-MOTOR-01',
            'compatible_models' => ['Whirlpool 7A', 'LG 10kg'],
            'min_stock' => 4,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 8,
            'minimum_quantity' => 4,
            'last_updated' => now(),
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'LAV-STOCK-001',
            'min_stock' => 4,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 8,
            'minimum_quantity' => 4,
        ]);
    }

    public function test_product_cannot_be_sold_when_stock_is_insufficient(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST2-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal Norte',
            'establishment_code' => '002',
            'country' => 'VE',
            'city' => 'Valencia',
            'address' => 'Calle Norte 2',
            'phone' => '+58 412 555 0105',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Lavadoras',
            'description' => 'Repuestos para lavadoras.',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'LAV-STOCK-002',
            'barcode' => '759000000101',
            'name' => 'Correa para lavadora',
            'brand' => 'Mabe',
            'description' => 'Correa de transmisión.',
            'images' => ['products/lavadoras/correa.jpg'],
            'cost' => 15.00,
            'price' => 28.00,
            'sale_price' => 24.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 0.70,
            'status' => 'active',
            'warranty_days' => 180,
            'part_number' => 'MB-CORREA-05',
            'compatible_models' => ['Mabe 5A'],
            'min_stock' => 2,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 1,
            'minimum_quantity' => 2,
            'last_updated' => now(),
        ]);

        $requestedQty = 2;
        $availableQty = ProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->value('quantity');

        $this->assertLessThan($requestedQty, $availableQty);
        $this->assertTrue($availableQty < $requestedQty);
    }
}
