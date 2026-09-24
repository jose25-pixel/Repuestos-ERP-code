<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductStock;
use Tests\TestCase;

class ProductEcommerceFieldsTest extends TestCase
{
    public function test_product_and_stock_can_store_ecommerce_fields(): void
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
            'sku' => 'LAV-PRUEBA-001',
            'barcode' => '759000000099',
            'name' => 'Bomba de desagüe para lavadora',
            'brand' => 'Whirlpool',
            'description' => 'Bomba compatible con lavadoras de carga superior.',
            'images' => ['products/lavadoras/bomba-desague.jpg'],
            'cost' => 4.50,
            'price' => 8.95,
            'sale_price' => 7.95,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 1.15,
            'status' => 'active',
            'warranty_days' => 180,
            'part_number' => 'WH-120',
            'compatible_models' => ['Whirlpool 7A', 'LG 10kg'],
            'min_stock' => 5,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 12,
            'minimum_quantity' => 5,
            'last_updated' => now(),
        ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'LAV-PRUEBA-001',
            'currency' => 'USD',
            'status' => 'active',
            'warranty_days' => 180,
            'min_stock' => 5,
        ]);

        $this->assertSame('7.95', (string) $product->sale_price);
        $this->assertSame(['Whirlpool 7A', 'LG 10kg'], $product->compatible_models);
        $this->assertSame(['products/lavadoras/bomba-desague.jpg'], $product->images);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 12,
            'minimum_quantity' => 5,
        ]);
    }
}
