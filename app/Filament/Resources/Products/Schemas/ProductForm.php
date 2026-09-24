<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Branch;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->live()
                    ->afterStateUpdated(function (?int $state, Set $set): void {
                        $set('stock_inicial', Branch::query()
                            ->where('company_id', $state)
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get()
                            ->map(fn (Branch $branch): array => [
                                'sucursal_id' => $branch->id,
                                'sucursal_nombre' => $branch->name,
                                'cantidad_inicial' => 0,
                                'cantidad_minima' => 0,
                            ])
                            ->all());
                    })
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
                TextInput::make('brand')
                    ->label('Marca')
                    ->placeholder('Ej. Whirlpool, Mabe, LG')
                    ->maxLength(120),
                TextInput::make('part_number')
                    ->label('Número de pieza / Parte')
                    ->maxLength(120)
                    ->helperText('Número del fabricante o referencia interna.'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
                Textarea::make('compatible_models')
                    ->label('Modelos compatibles')
                    ->rows(3)
                    ->helperText('Ej. Whirlpool 7A, LG 10kg, Samsung WW80J.')
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
                TextInput::make('weight_kg')
                    ->label('Peso (kg)')
                    ->numeric()
                    ->default(0)
                    ->suffix('kg')
                    ->helperText('Si pesa 10 kg o más, el sistema lo marca como pesado y suma cargo extra de envío.'),
                Toggle::make('is_heavy')
                    ->label('Producto pesado')
                    ->default(fn ($record) => $record?->weight_kg >= 10)
                    ->helperText('Se activa automáticamente si el peso es 10 kg o más.'),
                TextInput::make('precio_costo')
                    ->label('Precio de costo')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('precio_venta_sugerido')
                    ->label('Precio de venta sugerido (IVA incluido)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->prefix('$'),
                Toggle::make('exento_iva')
                    ->label('Exento de IVA')
                    ->default(false),
                TextInput::make('sale_price')
                    ->label('Precio de oferta')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->helperText('Precio especial si aplica una promoción.'),
                TextInput::make('regular_price')
                    ->label('Precio normal (opcional)')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('$')
                    ->helperText('Si es mayor que el precio de venta, se mostrará como promoción.'),
                TextInput::make('currency')
                    ->label('Moneda')
                    ->default('USD')
                    ->maxLength(3)
                    ->required(),
                Toggle::make('tax_included')
                    ->label('Precio incluye impuesto')
                    ->default(true)
                    ->hidden(),
                Select::make('status')
                    ->label('Estado del producto')
                    ->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'discontinued' => 'Descontinuado',
                    ])
                    ->default('active')
                    ->required(),
                TextInput::make('warranty_days')
                    ->label('Garantía (días)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Ejemplo: 180 días de garantía.'),
                TextInput::make('min_stock')
                    ->label('Stock mínimo')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->helperText('Cantidad mínima para alertar reabastecimiento.'),
                Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
                Repeater::make('stock_inicial')
                    ->label('Stock inicial por sucursal')
                    ->helperText('Ingresa cantidades solo si ya existe mercadería. Cada valor mayor que cero se registrará en el kardex como carga inicial.')
                    ->schema([
                        Hidden::make('sucursal_id'),
                        TextInput::make('sucursal_nombre')
                            ->label('Sucursal')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('cantidad_inicial')
                            ->label('Cantidad inicial')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        TextInput::make('cantidad_minima')
                            ->label('Mínimo para alerta')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(3)
                    ->default(fn (): array => Branch::query()
                        ->where('company_id', auth()->user()?->company_id)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->map(fn (Branch $branch): array => [
                            'sucursal_id' => $branch->id,
                            'sucursal_nombre' => $branch->name,
                            'cantidad_inicial' => 0,
                            'cantidad_minima' => 0,
                        ])
                        ->all())
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
            ]);
    }
}
