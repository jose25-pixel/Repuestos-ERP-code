<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function afterCreate(): void
    {
        $owner = User::query()->create([
            'company_id' => $this->record->id,
            'name' => $this->data['owner_name'],
            'email' => $this->data['owner_email'],
            'password' => $this->data['owner_password'],
            'role' => 'dueño_empresa',
        ]);

        $owner->assignRole('dueño_empresa');
    }
}
