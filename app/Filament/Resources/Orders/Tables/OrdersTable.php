<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => Order::query()->with(['customer', 'items', 'payments']))
            ->columns([
                TextColumn::make('number')
                    ->label('Pedido')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label('Pago')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash_on_delivery' => 'Contra entrega',
                        'qr_transfer' => 'QR / Transferencia',
                        'paypal' => 'PayPal',
                        'card' => 'Visa / Mastercard',
                        'lightning' => 'Bitcoin Lightning',
                        default => $state ?? 'Sin método',
                    }),
                TextColumn::make('payment_status')
                    ->label('Estado pago')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'failed' => 'Fallido',
                        default => $state ?? 'Sin estado',
                    }),
                TextColumn::make('delivery_status')
                    ->label('Entrega')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendiente',
                        'assigned' => 'Asignado',
                        'in_transit' => 'En camino',
                        'delivered' => 'Entregado',
                        default => $state ?? 'Pendiente',
                    }),
                TextColumn::make('shipping_zone')
                    ->label('Departamento')
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->label('Envío')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->label('IVA registrado')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')
                    ->options([
                        'cash_on_delivery' => 'Contra entrega',
                        'qr_transfer' => 'QR / Transferencia',
                        'paypal' => 'PayPal',
                        'card' => 'Visa / Mastercard',
                        'lightning' => 'Bitcoin Lightning',
                    ]),
                SelectFilter::make('delivery_status')
                    ->options([
                        'pending' => 'Pendiente',
                        'assigned' => 'Asignado',
                        'in_transit' => 'En camino',
                        'delivered' => 'Entregado',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
