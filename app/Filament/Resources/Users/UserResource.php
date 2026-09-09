<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Administradores';

    protected static ?string $modelLabel = 'administrador';

    protected static ?string $pluralModelLabel = 'administradores';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'dueño_empresa', 'admin_sucursal']) ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'dueño_empresa', 'admin_sucursal']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'dueño_empresa', 'admin_sucursal']) ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return ! $record->hasRole('super_admin');
        }

        if ($user?->hasRole('dueño_empresa')) {
            return $record->company_id === $user->company_id && $record->hasAnyRole(['admin_sucursal', 'vendedor']);
        }

        return $user?->hasRole('admin_sucursal')
            && $record->company_id === $user->company_id
            && $record->branch_id === $user->branch_id
            && $record->hasRole('vendedor');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        $query->where('company_id', $user?->company_id);

        return $user?->hasRole('admin_sucursal')
            ? $query->where('branch_id', $user->branch_id)
            : $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
