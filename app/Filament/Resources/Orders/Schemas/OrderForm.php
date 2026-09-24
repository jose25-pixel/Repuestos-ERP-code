<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Branch;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sucursal_id')
                    ->label('Sucursal de venta')
                    ->options(fn (): array => Branch::query()
                        ->when(! auth()->user()?->isSuperAdmin(), fn ($query) => $query->where('company_id', auth()->user()?->company_id))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->default(fn () => auth()->user()?->branch_id)
                    ->disabled(fn () => auth()->user()?->hasAnyRole(['admin_sucursal', 'vendedor', 'tecnico']))
                    ->required(),
                TextInput::make('customer_name')
                    ->label('Cliente')
                    ->default('Cliente mostrador')
                    ->required(),
                TextInput::make('customer_email')
                    ->label('Correo del cliente')
                    ->email()
                    ->required(),
                Select::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'cash' => 'Efectivo',
                        'card' => 'Tarjeta',
                        'qr_transfer' => 'QR / Transferencia',
                    ])
                    ->default('cash')
                    ->required(),
                Repeater::make('items')
                    ->label('Productos')
                    ->schema([
                        Select::make('product_id')
                            ->label('Producto')
                            ->options(fn (): array => Product::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Product $product): array => [$product->id => $product->sku.' - '.$product->name])
                                ->all())
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->required(),
            ]);
    }
}
