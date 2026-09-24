<?php

namespace App\Filament\Resources\ProductStocks\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\ProductStocks\ProductStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductStocks extends ListRecords
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear existencia'),
            CreateAction::make('createProduct')
                ->label('Ingresar producto')
                ->url(ProductResource::getUrl('create')),
        ];
    }
}
