<?php

namespace App\Filament\Resources\InventoryMovements\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entry' => 'Entrada',
                        'sale' => 'Venta',
                        default => 'Ajuste',
                    })
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Cambio')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Antes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Después')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
