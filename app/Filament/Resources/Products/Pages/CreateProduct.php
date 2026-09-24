<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Branch;
use App\Models\Inventario;
use App\Services\InventarioService;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $stockInicial = [];

    protected function beforeCreate(): void
    {
        $this->stockInicial = $this->data['stock_inicial'] ?? [];
    }

    protected function afterCreate(): void
    {
        $service = app(InventarioService::class);

        foreach ($this->stockInicial as $stock) {
            $branch = Branch::query()->findOrFail($stock['sucursal_id']);

            if ((int) ($stock['cantidad_inicial'] ?? 0) > 0) {
                $service->registrarCargaInicial(
                    $this->record,
                    $branch,
                    (int) $stock['cantidad_inicial'],
                    auth()->user(),
                    'Carga inicial al crear el producto',
                );
            }

            Inventario::query()->updateOrCreate(
                [
                    'producto_id' => $this->record->id,
                    'sucursal_id' => $branch->id,
                ],
                ['cantidad_minima' => (int) ($stock['cantidad_minima'] ?? 0)],
            );
        }
    }
}
