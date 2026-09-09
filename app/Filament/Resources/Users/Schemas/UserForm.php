<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Select::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->whereKey(auth()->user()?->company_id))
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()?->company_id)
                    ->disabled(fn () => ! auth()->user()?->isSuperAdmin())
                    ->dehydrated()
                    ->required(),
                Select::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin() ? $query : $query->where('company_id', auth()->user()?->company_id))
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()?->branch_id)
                    ->disabled(fn () => auth()->user()?->hasRole('admin_sucursal'))
                    ->dehydrated(),
                Select::make('role')
                    ->label('Rol')
                    ->options(fn (): array => match (true) {
                        auth()->user()?->isSuperAdmin() => ['dueño_empresa' => 'Dueño de empresa'],
                        auth()->user()?->hasRole('dueño_empresa') => ['admin_sucursal' => 'Administrador de sucursal', 'vendedor' => 'Vendedor'],
                        default => ['vendedor' => 'Vendedor'],
                    })
                    ->required()
                    ->default(fn (): string => auth()->user()?->hasRole('admin_sucursal') ? 'vendedor' : 'dueño_empresa'),
            ]);
    }
}
