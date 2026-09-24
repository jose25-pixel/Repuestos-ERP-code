<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductStock;
use Tests\TestCase;

class ProductPricePromotionTest extends TestCase
{
    public function test_product_can_store_regular_and_sale_prices(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST3-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Casa Matriz',
            'establishment_code' => '003',
            'country' => 'VE',
            'city' => 'Caracas',
            'address' => 'Av. Principal, Local 3',
            'phone' => '+58 212 555 0110',
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
            'sku' => 'LAV-PROMO-001',
            'barcode' => '759000000200',
            'name' => 'Válvula de entrada para lavadora',
            'brand' => 'Samsung',
            'description' => 'Válvula de entrada compatible con lavadoras.',
            'images' => ['products/lavadoras/valvula.jpg'],
            'cost' => 12.00,
            'price' => 25.00,
            'sale_price' => 19.90,
            'regular_price' => 25.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 0.50,
            'status' => 'active',
            'warranty_days' => 180,
            'part_number' => 'SM-VALV-01',
            'compatible_models' => ['Samsung 7A'],
            'min_stock' => 3,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 10,
            'minimum_quantity' => 3,
            'last_updated' => now(),
        ]);

        $this->assertSame('25.00', (string) $product->regular_price);
        $this->assertSame('19.90', (string) $product->sale_price);
        $this->assertSame('USD', $product->currency);
        $this->assertDatabaseHas('products', [
            'sku' => 'LAV-PROMO-001',
            'sale_price' => '19.90',
            'regular_price' => '25.00',
            'currency' => 'USD',
        ]);
    }
}
