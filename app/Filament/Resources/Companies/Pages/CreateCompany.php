<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected string $generatedOwnerPassword = '';

    protected function beforeCreate(): void
    {
        if (($this->data['password_mode'] ?? 'automatic') === 'automatic' || blank($this->data['owner_password'] ?? null)) {
            $this->generatedOwnerPassword = Str::password(12);
            $this->data['owner_password'] = $this->generatedOwnerPassword;
        }
    }

    protected function afterCreate(): void
    {
        self::createDefaultBranchAndOwner($this->record, $this->data);

        if ($this->generatedOwnerPassword !== '') {
            Notification::make()
                ->title('Empresa creada correctamente')
                ->body('Contraseña temporal del dueño: '.$this->generatedOwnerPassword)
                ->persistent()
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return CompanyResource::getUrl('index');
    }

    public static function createDefaultBranchAndOwner(Company $company, array $data): User
    {
        $branch = Branch::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Sucursal principal',
            ],
            [
                'country' => $company->country ?? 'VE',
                'city' => 'Principal',
                'address' => 'Dirección por definir',
                'phone' => null,
                'is_active' => true,
            ]
        );

        $owner = User::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'password' => $data['owner_password'],
            'role' => 'dueño_empresa',
        ]);

        $owner->syncRoles(['dueño_empresa']);

        return $owner;
    }
}
