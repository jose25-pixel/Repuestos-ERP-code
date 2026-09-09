<?php

namespace App\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user !== null && ! $user->hasRole(User::ROLE_SUPER_ADMIN) && $user->company_id !== null) {
            $builder->where($model->qualifyColumn('company_id'), $user->company_id);
        }
    }
}
