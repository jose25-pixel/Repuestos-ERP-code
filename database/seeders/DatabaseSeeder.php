<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            User::ROLE_SUPER_ADMIN,
            'dueño_empresa',
            'admin_sucursal',
            'vendedor',
            'tecnico',
            'cliente',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        $company = Company::updateOrCreate(
            ['tax_id' => 'J-12345678-9', 'country' => 'VE'],
            [
                'name' => 'Repuestos Demo, C.A.',
                'currency' => 'USD',
                'plan' => 'pro',
                'is_active' => true,
            ],
        );

        $branch = Branch::updateOrCreate(
            ['company_id' => $company->id, 'establishment_code' => '001'],
            [
                'name' => 'Casa Matriz',
                'country' => 'VE',
                'city' => 'Caracas',
                'address' => 'Av. Principal, Local 1',
                'phone' => '+58 212 555 0100',
                'is_active' => true,
            ],
        );

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'company_id' => null,
                'branch_id' => null,
                'name' => 'Super Administrador',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
        );
        $superAdmin->syncRoles([User::ROLE_SUPER_ADMIN]);

        $companyAdmin = User::updateOrCreate(
            ['email' => 'admin@repuestos-demo.test'],
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Administrador Demo',
                'password' => 'password',
                'role' => 'dueño_empresa',
            ],
        );
        $companyAdmin->syncRoles(['dueño_empresa']);

        $seller = User::updateOrCreate(
            ['email' => 'vendedor@repuestos-demo.test'],
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Vendedor Demo',
                'password' => 'password',
                'role' => 'vendedor',
            ],
        );
        $seller->syncRoles(['vendedor']);

        $customerUser = User::updateOrCreate(
            ['email' => 'cliente@ejemplo.test'],
            [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'name' => 'Cliente de Prueba',
                'password' => 'password',
                'role' => 'cliente',
            ],
        );
        $customerUser->syncRoles(['cliente']);

        Category::query()
            ->where('company_id', $company->id)
            ->whereIn('name', ['Motor', 'Frenos', 'Repuestos de lavadora', 'Accesorios de lavadora', 'Secadoras'])
            ->delete();

        $washersCategory = Category::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Lavadoras'],
            ['description' => 'Repuestos y componentes para lavadoras.'],
        );
        $dryersCategory = Category::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Secadoras'],
            ['description' => 'Repuestos y componentes para secadoras.'],
        );
        $refrigeratorPartsCategory = Category::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Repuestos de refrigeradora'],
            ['description' => 'Compresores, termostatos y componentes para refrigeradoras.'],
        );
        $refrigeratorAccessoriesCategory = Category::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Accesorios de refrigeradora'],
            ['description' => 'Accesorios y piezas externas para refrigeradoras.'],
        );
        $laundryAccessoriesCategory = Category::updateOrCreate(
            ['company_id' => $company->id, 'name' => 'Accesorios de lavadora'],
            ['description' => 'Accesorios y piezas externas para lavadoras.'],
        );

        $products = [
            [
                'category_id' => $washersCategory->id,
                'sku' => 'FIL-ACE-001',
                'barcode' => '759000000001',
                'name' => 'Bomba de desagüe para lavadora',
                'brand' => 'Whirlpool',
                'description' => 'Bomba compatible con lavadoras de carga superior.',
                'cost' => 4.50,
                'price' => 8.95,
                'sale_price' => 7.95,
                'currency' => 'USD',
                'tax_included' => false,
                'weight_kg' => 1.15,
                'status' => 'active',
                'warranty_days' => 180,
                'min_stock' => 5,
                'compatible_models' => ['Whirlpool', 'LG', 'Samsung'],
                'images' => ['products/lavadoras/bomba-desague.jpg'],
                'quantity' => 25,
                'minimum_quantity' => 5,
            ],
            [
                'category_id' => $washersCategory->id,
                'sku' => 'PAS-DEL-001',
                'barcode' => '759000000002',
                'name' => 'Correa para lavadora',
                'brand' => 'Mabe',
                'description' => 'Correa de transmisión para lavadora automática.',
                'cost' => 18.00,
                'price' => 34.90,
                'sale_price' => 29.90,
                'currency' => 'USD',
                'tax_included' => false,
                'weight_kg' => 0.70,
                'status' => 'active',
                'warranty_days' => 365,
                'min_stock' => 3,
                'compatible_models' => ['Mabe', 'General Electric', 'Whirlpool'],
                'images' => ['products/lavadoras/correa-lavadora.jpg'],
                'quantity' => 8,
                'minimum_quantity' => 3,
            ],
            [
                'category_id' => $laundryAccessoriesCategory->id,
                'sku' => 'BUJ-NGK-001',
                'barcode' => '759000000003',
                'name' => 'Perilla selectora de lavadora',
                'brand' => 'LG',
                'description' => 'Perilla de reemplazo para selector de ciclos.',
                'cost' => 3.25,
                'price' => 6.50,
                'sale_price' => 5.90,
                'currency' => 'USD',
                'tax_included' => false,
                'weight_kg' => 0.12,
                'status' => 'active',
                'warranty_days' => 90,
                'min_stock' => 10,
                'compatible_models' => ['LG', 'Samsung', 'Whirlpool'],
                'images' => ['products/lavadoras/perilla-selectora.jpg'],
                'quantity' => 40,
                'minimum_quantity' => 10,
            ],
        ];

        $seededProducts = [];
        foreach ($products as $productData) {
            $stockData = [
                'branch_id' => $branch->id,
                'quantity' => $productData['quantity'],
                'minimum_quantity' => $productData['minimum_quantity'],
                'last_updated' => now(),
            ];
            unset($productData['quantity'], $productData['minimum_quantity']);

            $product = Product::updateOrCreate(
                ['company_id' => $company->id, 'sku' => $productData['sku']],
                $productData + ['is_active' => true],
            );
            ProductStock::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branch->id],
                $stockData,
            );
            $seededProducts[] = $product;
        }

        $customer = Customer::updateOrCreate(
            ['email' => 'cliente@ejemplo.test'],
            [
                'name' => 'Cliente de Prueba',
                'phone' => '+58 414 555 0100',
            ],
        );

        $firstProduct = $seededProducts[0];
        $secondProduct = $seededProducts[1];
        $order = Order::updateOrCreate(
            ['number' => 'ORD-DEMO-0001'],
            [
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'status' => 'paid',
                'currency' => 'USD',
                'subtotal' => 52.80,
                'total' => 52.80,
            ],
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $firstProduct->id],
            [
                'product_name' => $firstProduct->name,
                'sku' => $firstProduct->sku,
                'unit_price' => $firstProduct->price,
                'quantity' => 2,
                'total' => 17.90,
            ],
        );
        OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $secondProduct->id],
            [
                'product_name' => $secondProduct->name,
                'sku' => $secondProduct->sku,
                'unit_price' => $secondProduct->price,
                'quantity' => 1,
                'total' => 34.90,
            ],
        );
        Payment::updateOrCreate(
            ['order_id' => $order->id, 'reference' => 'DEMO-TRANSFER-0001'],
            [
                'method' => 'bank_transfer',
                'status' => 'completed',
                'amount' => 52.80,
                'currency' => 'USD',
            ],
        );

        InventoryMovement::updateOrCreate(
            [
                'product_id' => $firstProduct->id,
                'reference_type' => 'seed',
                'reference_id' => $order->id,
            ],
            [
                'user_id' => $seller->id,
                'type' => 'sale',
                'quantity' => -2,
                'quantity_before' => 27,
                'quantity_after' => 25,
                'reason' => 'Venta de demostracion',
            ],
        );

    }
}
