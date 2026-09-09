<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],
            'payment_method' => ['required', Rule::in(['card', 'bank', 'lightning'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $order = DB::transaction(function () use ($validated): Order {
            $items = collect($validated['items']);
            $products = Product::query()
                ->whereIn('id', $items->pluck('id'))
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                abort(422, 'Uno o más productos ya no están disponibles.');
            }

            $companyId = $products->first()->company_id;

            if ($products->contains(fn (Product $product): bool => $product->company_id !== $companyId)) {
                abort(422, 'Una orden solo puede contener productos de una empresa.');
            }

            $customer = Customer::query()->updateOrCreate(
                ['email' => $validated['customer']['email']],
                $validated['customer'],
            );
            $subtotal = 0;
            $order = Order::query()->create([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'number' => 'ORD-'.now()->format('YmdHis').'-'.str()->upper(str()->random(5)),
                'status' => 'pending_payment',
                'currency' => $products->first()->company->currency,
                'subtotal' => 0,
                'total' => 0,
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => $lineTotal,
                ]);

                InventoryMovement::query()->create([
                    'product_id' => $product->id,
                    'type' => InventoryMovement::TYPE_SALE,
                    'quantity' => -$item['quantity'],
                    'reference_type' => Order::class,
                    'reference_id' => $order->id,
                    'reason' => "Venta {$order->number}",
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);
            $order->payments()->create([
                'method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $subtotal,
                'currency' => $order->currency,
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Orden creada. El pago está pendiente de confirmación.',
            'order_number' => $order->number,
        ], 201);
    }
}
