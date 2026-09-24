<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('logo_path')
                    ->label('Logo de la empresa')
                    ->disk('public')
                    ->directory('companies')
                    ->image()
                    ->maxSize(2048)
                    ->helperText('Sube el logo principal de la empresa para mostrarlo en el menú y la tienda.'),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required(),
                TextInput::make('tax_id')
                    ->label('Identificación fiscal'),
                TextInput::make('country')
                    ->label('País')
                    ->length(2)
                    ->uppercase()
                    ->required(),
                TextInput::make('currency')
                    ->label('Moneda')
                    ->length(3)
                    ->uppercase()
                    ->required()
                    ->default('USD'),
                Select::make('plan')
                    ->label('Plan')
                    ->options(['basic' => 'Básico', 'professional' => 'Profesional', 'enterprise' => 'Empresarial'])
                    ->required()
                    ->default('basic'),
                Toggle::make('is_active')
                    ->label('Empresa activa')
                    ->default(true)
                    ->required(),
                TextInput::make('owner_name')
                    ->label('Nombre del dueño inicial')
                    ->required()
                    ->dehydrated(false),
                TextInput::make('owner_email')
                    ->label('Correo del dueño inicial')
                    ->email()
                    ->required()
                    ->unique('users', 'email')
                    ->dehydrated(false),
                TextInput::make('owner_password')
                    ->label('Contraseña inicial del dueño')
                    ->password()
                    ->minLength(8)
                    ->required()
                    ->dehydrated(false),
            ]);
    }
}
