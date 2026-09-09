<?php

namespace App\Filament\Resources\ProductStocks\Pages;

use App\Filament\Resources\ProductStocks\ProductStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductStock extends EditRecord
{
    protected static string $resource = ProductStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
