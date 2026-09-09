<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('plan')->default('basic')->after('currency');
            $table->boolean('is_active')->default(true)->after('plan');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->string('establishment_code', 20)->nullable()->after('name');
            $table->unique(['company_id', 'establishment_code']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
        });

        foreach ([User::ROLE_SUPER_ADMIN, 'dueño_empresa', 'admin_sucursal', 'vendedor', 'tecnico', 'cliente'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        User::query()->each(function (User $user): void {
            $role = $user->role === User::ROLE_SUPER_ADMIN ? User::ROLE_SUPER_ADMIN : 'dueño_empresa';
            $user->syncRoles([$role]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'establishment_code']);
            $table->dropColumn('establishment_code');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['plan', 'is_active']);
        });
    }
};
