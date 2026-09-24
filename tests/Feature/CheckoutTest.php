<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_checkout_requires_customer_payment_method_and_items(): void
    {
        $this->postJson(route('checkout.store'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer.name', 'customer.email', 'payment_method', 'items']);
    }

    public function test_checkout_creates_a_pending_order_and_records_inventory_sale(): void
    {
        DB::beginTransaction();

        try {
            $suffix = Str::uuid()->toString();
            $company = Company::query()->create([
                'name' => "Empresa {$suffix}",
                'country' => 'SV',
                'currency' => 'USD',
            ]);
            $product = Product::query()->create([
                'company_id' => $company->id,
                'sku' => "SKU-{$suffix}",
                'name' => 'Bomba de prueba',
                'cost' => 5,
                'price' => 10,
                'is_active' => true,
            ]);
            $stock = ProductStock::query()->create([
                'product_id' => $product->id,
                'quantity' => 5,
                'minimum_quantity' => 1,
            ]);

            $this->assertSame(
                sprintf('RERP-%05d-%08d', $company->id, $product->id),
                $product->fresh()->barcode,
            );

            $this->postJson(route('checkout.store'), [
                'customer' => ['name' => 'Cliente de prueba', 'email' => "cliente-{$suffix}@example.test"],
                'payment_method' => 'lightning',
                'items' => [['id' => $product->id, 'quantity' => 2]],
            ])->assertCreated()->assertJsonPath('message', 'Orden creada. El pago está pendiente de confirmación.');

            $this->assertSame(3, $stock->fresh()->quantity);
            $this->assertDatabaseHas('inventory_movements', [
                'product_id' => $product->id,
                'type' => 'sale',
                'quantity' => -2,
                'quantity_before' => 5,
                'quantity_after' => 3,
            ]);
        } finally {
            DB::rollBack();
        }
    }

    public function test_checkout_values_shipping_zone_and_cash_on_delivery_flow(): void
    {
        DB::beginTransaction();

        try {
            $suffix = Str::uuid()->toString();
            $company = Company::query()->create([
                'name' => "Empresa envío {$suffix}",
                'country' => 'SV',
                'currency' => 'USD',
            ]);
            $product = Product::query()->create([
                'company_id' => $company->id,
                'sku' => "SKU-ENV-{$suffix}",
                'name' => 'Motor de prueba',
                'cost' => 8,
                'price' => 20,
                'is_active' => true,
            ]);
            ProductStock::query()->create([
                'product_id' => $product->id,
                'quantity' => 10,
                'minimum_quantity' => 1,
            ]);

            $this->postJson(route('checkout.store'), [
                'customer' => [
                    'name' => 'Cliente con envío',
                    'email' => "envio-{$suffix}@example.test",
                    'phone' => '7000-0000',
                ],
                'payment_method' => 'cash_on_delivery',
                'shipping_zone' => 'la_union',
                'shipping_cost' => 6,
                'items' => [['id' => $product->id, 'quantity' => 6]],
            ])->assertCreated();

            $this->assertDatabaseHas('orders', [
                'payment_method' => 'cash_on_delivery',
                'shipping_zone' => 'la_union',
                'shipping_cost' => 6.00,
            ]);
        } finally {
            DB::rollBack();
        }
    }
}
