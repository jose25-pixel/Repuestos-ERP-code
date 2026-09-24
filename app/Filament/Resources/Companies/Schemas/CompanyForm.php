<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->label('Identificación fiscal')
                    ->unique('companies', 'tax_id', ignoreRecord: true)
                    ->helperText('Debe ser diferente para cada empresa.'),
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
                Select::make('currency')
                    ->label('Moneda')
                    ->options([
                        'USD' => 'USD - Dólar estadounidense',
                        'EUR' => 'EUR - Euro',
                        'VES' => 'VES - Bolívar venezolano',
                        'COP' => 'COP - Peso colombiano',
                        'MXN' => 'MXN - Peso mexicano',
                        'CLP' => 'CLP - Peso chileno',
                        'PEN' => 'PEN - Sol peruano',
                        'BRL' => 'BRL - Real brasileño',
                        'ARS' => 'ARS - Peso argentino',
                        'GTQ' => 'GTQ - Quetzal guatemalteco',
                        'HNL' => 'HNL - Lempira hondureño',
                        'NIO' => 'NIO - Córdoba nicaragüense',
                        'CRC' => 'CRC - Colón costarricense',
                        'PAB' => 'PAB - Balboa panameño',
                        'DOP' => 'DOP - Peso dominicano',
                    ])
                    ->searchable()
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
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                TextInput::make('owner_email')
                    ->label('Correo del dueño inicial')
                    ->email()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->unique('users', 'email')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                Radio::make('password_mode')
                    ->label('Contraseña inicial del dueño')
                    ->options([
                        'automatic' => 'Usar contraseña generada automáticamente',
                        'manual' => 'Escribir una contraseña manualmente',
                    ])
                    ->default('automatic')
                    ->live()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false),
                TextInput::make('owner_password')
                    ->label('Contraseña inicial del dueño')
                    ->password()
                    ->minLength(8)
                    ->required(fn (Get $get, string $operation): bool => $operation === 'create' && $get('password_mode') === 'manual')
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && $get('password_mode') === 'manual')
                    ->dehydrated(false),
            ]);
    }
}
