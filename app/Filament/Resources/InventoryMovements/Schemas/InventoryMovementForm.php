<?php

namespace App\Filament\Resources\InventoryMovements\Schemas;

use App\Models\InventoryMovement;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Producto o código escaneado')
                    ->relationship('product', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->where('company_id', auth()->user()?->company_id))
                    ->getOptionLabelFromRecordUsing(fn (Product $product): string => "{$product->barcode} · {$product->name}")
                    ->searchable(['barcode', 'sku', 'name'])
                    ->searchPrompt('Escanee el código o escriba el SKU')
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->label('Tipo de movimiento')
                    ->options([
                        InventoryMovement::TYPE_ENTRY => 'Entrada de mercadería',
                        InventoryMovement::TYPE_ADJUSTMENT => 'Ajuste de inventario',
                    ])
                    ->required(),
                TextInput::make('quantity')
                    ->label('Cantidad')
                    ->helperText('Use números positivos para entradas; en ajustes puede usar negativos.')
                    ->required()
                    ->numeric(),
                TextInput::make('reason')
                    ->label('Motivo o referencia')
                    ->required(),
            ]);
    }
}
