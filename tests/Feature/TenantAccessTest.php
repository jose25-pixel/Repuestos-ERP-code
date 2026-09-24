<?php

namespace Tests\Feature;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantAccessTest extends TestCase
{
    public function test_company_creation_creates_default_branch_and_owner(): void
    {
        $company = Company::create([
            'name' => 'Empresa Demo',
            'tax_id' => 'J-'.uniqid('COMP-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'basic',
            'is_active' => true,
        ]);

        $email = 'ana-'.uniqid().'@demo.com';
        $owner = CreateCompany::createDefaultBranchAndOwner($company, [
            'owner_name' => 'Ana Gómez',
            'owner_email' => $email,
            'owner_password' => 'secret123',
        ]);

        $branch = Branch::query()->where('company_id', $company->id)->first();

        $this->assertNotNull($branch);
        $this->assertSame('Sucursal principal', $branch->name);

        $this->assertDatabaseHas('users', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'email' => $email,
        ]);

        $this->assertTrue($owner->hasRole('dueño_empresa'));
    }

    public function test_company_owner_only_sees_its_company(): void
    {
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('dueño_empresa', 'web');

        $companyA = Company::create([
            'name' => 'Empresa A',
            'tax_id' => 'J-'.uniqid('A-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'basic',
            'is_active' => true,
        ]);

        $companyB = Company::create([
            'name' => 'Empresa B',
            'tax_id' => 'J-'.uniqid('B-', true),
            'country' => 'MX',
            'currency' => 'MXN',
            'plan' => 'basic',
            'is_active' => true,
        ]);

        $branchA = Branch::create([
            'company_id' => $companyA->id,
            'name' => 'Sucursal A',
            'country' => 'VE',
            'city' => 'Caracas',
            'address' => 'Calle A',
            'phone' => '+58 412 000 0101',
            'is_active' => true,
        ]);

        $owner = User::create([
            'company_id' => $companyA->id,
            'branch_id' => $branchA->id,
            'name' => 'Owner A',
            'email' => 'ownerA-'.uniqid().'@demo.com',
            'password' => bcrypt('secret123'),
        ]);
        $owner->syncRoles(['dueño_empresa']);

        $this->actingAs($owner);

        $visibleCompanyIds = CompanyResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($companyA->id, $visibleCompanyIds);
        $this->assertNotContains($companyB->id, $visibleCompanyIds);
    }

    public function test_super_admin_can_open_company_creation_and_owner_can_enter_panel(): void
    {
        Role::findOrCreate('super_admin', 'web');

        $superAdmin = User::query()->where('role', 'super_admin')->first();

        if ($superAdmin === null) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'super-'.uniqid().'@demo.com',
                'password' => bcrypt('secret123'),
                'role' => 'super_admin',
            ]);
        }

        $superAdmin->syncRoles(['super_admin']);

        $this->actingAs($superAdmin, 'web')
            ->get('/admin/companies/create')
            ->assertOk();

        $company = Company::create([
            'name' => 'Empresa Panel',
            'tax_id' => 'J-'.uniqid('PANEL-', true),
            'country' => 'VE',
            'currency' => 'USD',
            'plan' => 'basic',
            'is_active' => true,
        ]);

        $owner = CreateCompany::createDefaultBranchAndOwner($company, [
            'owner_name' => 'Dueño Panel',
            'owner_email' => 'owner-'.uniqid().'@demo.com',
            'owner_password' => 'secret123',
        ]);

        $this->assertTrue($this->actingAs($superAdmin, 'web')->get('/admin')->isSuccessful());

        $this->actingAs($owner, 'web');
        $this->assertAuthenticatedAs($owner, 'web');

        $ownerResponse = $this->get('/admin/companies');

        $this->assertSame(
            200,
            $ownerResponse->getStatusCode(),
            'Redirección del dueño: '.$ownerResponse->headers->get('Location')
        );
    }
}
