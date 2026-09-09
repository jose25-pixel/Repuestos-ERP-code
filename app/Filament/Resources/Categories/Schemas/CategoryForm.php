<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
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
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),
            ]);
    }
}
