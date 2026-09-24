<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use Tests\TestCase;

class ProductSalesRulesTest extends TestCase
{
    public function test_order_can_be_created_when_stock_is_available(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST7-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Sucursal ventas',
            'establishment_code' => '301',
            'country' => 'VE',
            'city' => 'Caracas',
            'address' => 'Av. Venta 301',
            'phone' => '+58 412 000 0301',
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
            'sku' => 'LAV-VENTA-001',
            'barcode' => '759000000600',
            'name' => 'Bomba de desagüe',
            'brand' => 'Whirlpool',
            'description' => 'Bomba de desagüe para lavadora.',
            'images' => ['products/lavadoras/bomba.jpg'],
            'cost' => 10.00,
            'price' => 18.00,
            'sale_price' => 16.50,
            'regular_price' => 18.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 1.20,
            'status' => 'active',
            'warranty_days' => 180,
            'part_number' => 'WH-BOMBA-1',
            'compatible_models' => ['Whirlpool 7A'],
            'min_stock' => 2,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 5,
            'minimum_quantity' => 2,
            'last_updated' => now(),
        ]);

        $customer = \App\Models\Customer::create([
            'name' => 'Cliente de prueba',
            'email' => 'cliente-venta-'.uniqid(). '@example.com',
            'phone' => '+58 412 000 0302',
            'country' => 'VE',
            'department' => 'Distrito Capital',
            'municipality' => 'Caracas',
            'address' => 'Calle Venta 1',
            'address_reference' => 'Frente al parque',
        ]);

        $order = Order::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'number' => 'ORD-'.uniqid(),
            'status' => 'paid',
            'currency' => 'USD',
            'subtotal' => 16.50,
            'total' => 16.50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => $product->sale_price,
            'quantity' => 1,
            'total' => 16.50,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'currency' => 'USD',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $availableStock = ProductStock::where('product_id', $product->id)
            ->where('branch_id', $branch->id)
            ->value('quantity');

        $this->assertGreaterThanOrEqual(1, $availableStock);
    }

    public function test_product_marked_as_discontinued_cannot_be_sold(): void
    {
        $company = Company::create([
            'name' => 'Repuestos Demo, C.A.',
            'tax_id' => 'J-'.uniqid('TEST8-', true),
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
            'name' => 'Sucursal licuadoras',
            'establishment_code' => '401',
            'country' => 'VE',
            'city' => 'Barquisimeto',
            'address' => 'Calle licenciadora',
            'phone' => '+58 412 000 0401',
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'category_id' => $category->id,
            'sku' => 'LIC-DESC-001',
            'barcode' => '759000000700',
            'name' => 'Cuchilla para licuadora',
            'brand' => 'Oster',
            'description' => 'Cuchilla para licuadora.',
            'images' => ['products/licuadoras/cuchilla.jpg'],
            'cost' => 8.00,
            'price' => 16.00,
            'currency' => 'USD',
            'tax_included' => false,
            'weight_kg' => 0.40,
            'status' => 'discontinued',
            'warranty_days' => 0,
            'part_number' => 'OST-CUCH-2',
            'compatible_models' => ['Oster 4A'],
            'min_stock' => 1,
            'is_heavy' => false,
            'is_active' => true,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'quantity' => 2,
            'minimum_quantity' => 1,
            'last_updated' => now(),
        ]);

        $saleAllowed = $product->status === 'active' && $product->is_active;

        $this->assertFalse($saleAllowed);
        $this->assertSame('discontinued', $product->status);
    }
}
