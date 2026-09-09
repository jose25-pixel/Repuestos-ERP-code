<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->whereKey(auth()->user()?->company_id))
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()?->company_id)
                    ->disabled(fn () => ! auth()->user()?->isSuperAdmin())
                    ->dehydrated()
                    ->required(),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->where('company_id', auth()->user()?->company_id))
                    ->searchable()
                    ->preload(),
                TextInput::make('sku')
                    ->label('SKU')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('barcode')
                    ->label('Código de barras')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Se genera automáticamente al crear el producto.'),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                FileUpload::make('images')
                    ->label('Imágenes del producto')
                    ->disk('public')
                    ->directory('products')
                    ->image()
                    ->multiple()
                    ->maxFiles(3)
                    ->reorderable()
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->label('Costo')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->required(),
            ]);
    }
}
