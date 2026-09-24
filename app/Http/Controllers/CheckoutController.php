<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Support\ShippingCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function bitcoinQuote(Request $request): JsonResponse
    {
        $amount = max(0, (float) $request->query('amount', 0));
        $response = Http::timeout(5)->get('https://api.coingecko.com/api/v3/simple/price', [
            'ids' => 'bitcoin',
            'vs_currencies' => 'usd',
        ]);

        if (! $response->successful() || ! is_numeric($response->json('bitcoin.usd'))) {
            return response()->json(['message' => 'No se pudo obtener la cotización de Bitcoin.'], 503);
        }

        $bitcoinUsd = (float) $response->json('bitcoin.usd');
        $bitcoinAmount = $bitcoinUsd > 0 ? $amount / $bitcoinUsd : 0;

        return response()->json([
            'usd' => round($amount, 2),
            'bitcoin_usd' => $bitcoinUsd,
            'bitcoin_amount' => round($bitcoinAmount, 8),
            'satoshis' => (int) round($bitcoinAmount * 100000000),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['required', 'string', 'max:30'],
            'customer.country' => ['required', 'string', 'max:120'],
            'customer.department' => ['required', 'string', 'max:120'],
            'customer.municipality' => ['required', 'string', 'max:120'],
            'customer.address' => ['required', 'string', 'max:255'],
            'customer.address_reference' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(['card', 'paypal', 'qr_transfer', 'cash_on_delivery', 'lightning'])],
            'shipping_zone' => ['nullable', 'string', 'max:80'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $shippingCost = (float) ($validated['shipping_cost'] ?? 0);
        $shippingZone = $validated['shipping_zone'] ?? strtolower($validated['customer']['department'] ?? 'local');

        $order = DB::transaction(function () use ($validated, $shippingZone, $shippingCost): Order {
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

            $totalWeight = $products->sum(fn (Product $product) => (float) ($product->weight_kg ?? 0) * ($validated['items'][array_search($product->id, array_column($validated['items'], 'id'))]['quantity'] ?? 1));
            $isHeavy = $products->contains(fn (Product $product) => (bool) $product->is_heavy);
            $calculatedShipping = ShippingCalculator::calculate(count($validated['items']), $totalWeight, $validated['customer']['department'], $isHeavy);

            $shippingCost = $calculatedShipping['cost'];
                $taxRate = (float) config('app.sales_tax_rate', 13);

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
                'payment_method' => $validated['payment_method'],
                'payment_status' => in_array($validated['payment_method'], ['cash_on_delivery', 'qr_transfer'], true) ? 'pending' : 'pending',
                'shipping_zone' => $shippingZone,
                'shipping_cost' => $shippingCost,
                'delivery_status' => 'pending',
                'subtotal' => 0,
                'tax_rate' => $taxRate,
                'tax_amount' => 0,
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

            $total = $subtotal + $shippingCost;
            $taxAmount = round($subtotal - ($subtotal / (1 + ($taxRate / 100))), 2);
            $order->update(['subtotal' => $subtotal, 'tax_amount' => $taxAmount, 'total' => $total]);
            $order->payments()->create([
                'method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $total,
                'currency' => $order->currency,
                'notes' => $shippingZone ? 'Zona de envío: '.$shippingZone : null,
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Orden creada. El pago está pendiente de confirmación.',
            'order_number' => $order->number,
        ], 201);
    }
}
