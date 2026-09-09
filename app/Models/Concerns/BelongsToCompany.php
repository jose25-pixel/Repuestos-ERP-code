<?php

namespace App\Models\Concerns;

use App\Scopes\EmpresaScope;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }
}
