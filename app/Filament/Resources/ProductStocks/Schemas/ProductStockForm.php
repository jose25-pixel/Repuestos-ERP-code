<?php

namespace App\Filament\Resources\ProductStocks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductStockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->where('company_id', auth()->user()?->company_id))
                    ->searchable()
                    ->preload()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('quantity')
                    ->label('Cantidad disponible')
                    ->helperText('Las variaciones se registran desde Movimientos.')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->disabled()
                    ->dehydrated()
                    ->default(0),
                TextInput::make('minimum_quantity')
                    ->label('Cantidad mínima')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
