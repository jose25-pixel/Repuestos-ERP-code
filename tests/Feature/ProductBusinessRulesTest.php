<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductStock;
use Tests\TestCase;

class ProductBusinessRulesTest extends TestCase
{
    public function test_product_stores_tax_status_warranty_and_compatibility(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST4-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Secadoras',
            'description' => 'Repuestos para secadoras.',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'SEC-TAX-001',
            'barcode' => '759000000300',
            'name' => 'Termostato para secadora',
            'brand' => 'LG',
            'description' => 'Termostato para secadora.',
            'images' => ['products/secadoras/termostato.jpg'],
            'cost' => 22.00,
            'price' => 49.90,
            'sale_price' => 42.50,
            'regular_price' => 49.90,
            'currency' => 'USD',
            'tax_included' => true,
            'weight_kg' => 0.90,
            'status' => 'active',
            'warranty_days' => 300,
            'part_number' => 'LG-TERM-55',
            'compatible_models' => ['LG 8kg', 'Samsung 7kg'],
            'min_stock' => 2,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        $this->assertTrue($product->tax_included);
        $this->assertSame('active', $product->status);
        $this->assertSame(300, $product->warranty_days);
        $this->assertSame(['LG 8kg', 'Samsung 7kg'], $product->compatible_models);
    }

    public function test_stock_is_tracked_by_branch_independently(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST5-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Refrigeradoras',
            'description' => 'Repuestos para refrigeradoras.',
        ]);

        $branchA = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal A',
            'establishment_code' => '101',
            'country' => 'VE',
            'city' => 'Caracas',
            'address' => 'Calle A',
            'phone' => '+58 412 000 0101',
            'is_active' => true,
        ]);

        $branchB = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal B',
            'establishment_code' => '102',
            'country' => 'VE',
            'city' => 'Valencia',
            'address' => 'Calle B',
            'phone' => '+58 412 000 0102',
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'REF-BRANCH-001',
            'barcode' => '759000000400',
            'name' => 'Compresor para refrigeradora',
            'brand' => 'Mabe',
            'description' => 'Compresor compatible con refrigeradoras.',
            'images' => ['products/refrigeradoras/compresor.jpg'],
            'cost' => 60.00,
            'price' => 120.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 12.50,
            'status' => 'active',
            'warranty_days' => 360,
            'part_number' => 'MB-COMP-08',
            'compatible_models' => ['Mabe 500L'],
            'min_stock' => 3,
            'is_heavy' => true,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branchA->id,
            'quantity' => 10,
            'minimum_quantity' => 3,
            'last_updated' => now(),
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branchB->id,
            'quantity' => 2,
            'minimum_quantity' => 2,
            'last_updated' => now(),
        ]);

        $branchAStock = ProductStock::where('product_id', $product->id)
            ->where('branch_id', $branchA->id)
            ->value('quantity');

        $branchBStock = ProductStock::where('product_id', $product->id)
            ->where('branch_id', $branchB->id)
            ->value('quantity');

        $this->assertSame(10, $branchAStock);
        $this->assertSame(2, $branchBStock);
        $this->assertNotSame($branchAStock, $branchBStock);
    }

    public function test_out_of_stock_product_reports_zero_available_quantity(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST6-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $category = Category::create([
            'company_id' => $company->id,
            'name' => 'Licuadoras',
            'description' => 'Repuestos para licuadoras.',
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal centro',
            'establishment_code' => '201',
            'country' => 'VE',
            'city' => 'Maracaibo',
            'address' => 'Calle centro',
            'phone' => '+58 412 000 0201',
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'LIC-OUT-001',
            'barcode' => '759000000500',
            'name' => 'Motor para licuadora',
            'brand' => 'Oster',
            'description' => 'Motor para licuadora.',
            'images' => ['products/licuadoras/motor.jpg'],
            'cost' => 18.00,
            'price' => 38.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 1.80,
            'status' => 'active',
            'warranty_days' => 120,
            'part_number' => 'OS-MOT-11',
            'compatible_models' => ['Oster 5A'],
            'min_stock' => 1,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 0,
            'minimum_quantity' => 1,
            'last_updated' => now(),
        ]);

        $availableQty = ProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->value('quantity');

        $this->assertSame(0, $availableQty);
        $this->assertTrue($availableQty === 0);
    }
}
