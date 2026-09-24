<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Inventario;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventarioService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        $shippingCost = 5.00;
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

            $dispatchBranch = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->get()
                ->first(fn (Branch $branch): bool => $items->every(fn (array $item): bool =>
                    (int) Inventario::query()
                        ->where('producto_id', $item['id'])
                        ->where('sucursal_id', $branch->id)
                        ->value('cantidad_disponible') >= (int) $item['quantity']
                ));

            if ($dispatchBranch === null) {
                abort(422, 'No existe una sucursal de despacho con stock suficiente para toda la orden.');
            }

            $company = $products->first()->company;
            $taxRate = (float) $company->porcentaje_iva;
            $customer = Customer::query()->updateOrCreate(
                ['email' => $validated['customer']['email']],
                $validated['customer'],
            );

            $order = Order::query()->create([
                'company_id' => $companyId,
                'sucursal_id' => $dispatchBranch->id,
                'customer_id' => $customer->id,
                'number' => 'ORD-'.now()->format('YmdHis').'-'.str()->upper(str()->random(5)),
                'status' => 'pending_payment',
                'canal' => 'online',
                'currency' => $company->currency,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'shipping_zone' => $shippingZone,
                'shipping_cost' => $shippingCost,
                'delivery_status' => 'pending',
                'subtotal' => 0,
                'tax_rate' => $taxRate,
                'tax_amount' => 0,
                'total' => 0,
            ]);

            $subtotal = 0.00;
            $taxAmount = 0.00;
            $inventory = app(InventarioService::class);

            foreach ($items as $item) {
                $product = $products->get($item['id']);
                $quantity = (int) $item['quantity'];
                $breakdown = $product->precioConDesglose($dispatchBranch->id);
                $lineTotal = $breakdown['precio_con_iva'] * $quantity;
                $subtotal += $lineTotal;
                $taxAmount += $breakdown['iva'] * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $breakdown['precio_con_iva'],
                    'quantity' => $quantity,
                    'total' => $lineTotal,
                ]);

                $inventory->registrarSalida(
                    $product,
                    $dispatchBranch,
                    $quantity,
                    $order->number,
                    null,
                    'Venta online',
                );
            }

            $total = $subtotal + $shippingCost;
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ]);

            $order->payments()->create([
                'method' => $validated['payment_method'],
                'status' => 'pending',
                'amount' => $total,
                'currency' => $order->currency,
                'notes' => 'Envío online: USD 5. Zona: '.$shippingZone,
            ]);

            return $order;
        });

        return response()->json([
            'message' => 'Orden creada. El pago está pendiente de confirmación.',
            'order_number' => $order->number,
        ], 201);
    }
}
