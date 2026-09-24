<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\InventarioService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $user = auth()->user();
        $branch = Branch::query()->findOrFail($data['sucursal_id']);
        $items = collect($data['items']);

        if (! $user?->isSuperAdmin() && $branch->company_id !== $user?->company_id) {
            throw ValidationException::withMessages(['sucursal_id' => 'La sucursal no pertenece a tu empresa.']);
        }

        return DB::transaction(function () use ($data, $branch, $items, $user): Order {
            $products = \App\Models\Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages(['items' => 'Uno o más productos no están disponibles.']);
            }

            if ($products->contains(fn ($product): bool => $product->company_id !== $branch->company_id)) {
                throw ValidationException::withMessages(['items' => 'Todos los productos deben pertenecer a la empresa de la sucursal.']);
            }

            $company = $branch->company;
            $customer = Customer::query()->updateOrCreate(
                ['email' => $data['customer_email']],
                ['name' => $data['customer_name']],
            );

            $order = Order::query()->create([
                'company_id' => $branch->company_id,
                'sucursal_id' => $branch->id,
                'customer_id' => $customer->id,
                'number' => 'POS-'.now()->format('YmdHis').'-'.str()->upper(str()->random(5)),
                'status' => 'completed',
                'canal' => 'pos',
                'currency' => $company->currency,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'shipping_zone' => null,
                'shipping_cost' => 0,
                'delivery_status' => 'delivered',
                'subtotal' => 0,
                'tax_rate' => $company->porcentaje_iva,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            $subtotal = 0.00;
            $taxAmount = 0.00;
            $inventory = app(InventarioService::class);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $breakdown = $product->precioConDesglose($branch->id);
                $lineTotal = $breakdown['precio_con_iva'] * $quantity;

                $inventory->registrarSalida($product, $branch, $quantity, $order->number, $user, 'Venta POS');

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $breakdown['precio_con_iva'],
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;
                $taxAmount += $breakdown['iva'] * $quantity;
            }

            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal,
            ]);

            $order->payments()->create([
                'method' => $data['payment_method'],
                'status' => 'paid',
                'amount' => $subtotal,
                'currency' => $company->currency,
            ]);

            return $order;
        });
    }
}
