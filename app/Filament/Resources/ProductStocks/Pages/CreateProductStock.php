<?php

namespace App\Filament\Resources\ProductStocks\Pages;

use App\Filament\Resources\ProductStocks\ProductStockResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductStock extends CreateRecord
{
    protected static string $resource = ProductStockResource::class;
}
