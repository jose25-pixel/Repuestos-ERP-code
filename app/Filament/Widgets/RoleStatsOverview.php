<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoleStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return [
                Stat::make('Empresas activas', Company::query()->where('is_active', true)->count()),
                Stat::make('Sucursales totales', Branch::withoutGlobalScopes()->count()),
                Stat::make('Productos publicados', Product::withoutGlobalScopes()->where('is_active', true)->count()),
            ];
        }

        if ($user?->hasRole('admin_sucursal')) {
            return [
                Stat::make('Sucursal asignada', $user->branch?->name ?? 'Sin asignar'),
                Stat::make('Productos de la empresa', Product::query()->where('is_active', true)->count()),
            ];
        }

        return [
            Stat::make('Sucursales de la empresa', Branch::query()->count()),
            Stat::make('Productos de la empresa', Product::query()->where('is_active', true)->count()),
        ];
    }
}
