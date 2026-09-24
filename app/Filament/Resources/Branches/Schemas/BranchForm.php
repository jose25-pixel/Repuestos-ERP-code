<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
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
                TextInput::make('establishment_code')
                    ->label('Código de establecimiento DTE')
                    ->maxLength(20),
                Select::make('country')
                    ->label('País')
                    ->options([
                        'VE' => 'Venezuela',
                        'CO' => 'Colombia',
                        'MX' => 'México',
                        'SV' => 'El Salvador',
                        'GT' => 'Guatemala',
                        'HN' => 'Honduras',
                        'NI' => 'Nicaragua',
                        'CR' => 'Costa Rica',
                        'PA' => 'Panamá',
                        'DO' => 'República Dominicana',
                        'US' => 'Estados Unidos',
                        'ES' => 'España',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('city')
                    ->label('Ciudad'),
                TextInput::make('address')
                    ->label('Dirección'),
                TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel(),
                Toggle::make('is_active')
                    ->label('Activa')
                    ->required(),
            ]);
    }
}
