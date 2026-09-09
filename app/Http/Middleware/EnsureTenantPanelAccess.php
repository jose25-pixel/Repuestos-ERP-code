<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasAnyRole(['super_admin', 'dueño_empresa', 'admin_sucursal', 'vendedor', 'tecnico'])) {
            abort(403);
        }

        if (! $user->isSuperAdmin() && ($user->company === null || ! $user->company->is_active)) {
            abort(403, 'La empresa asignada no está activa.');
        }

        if ($user->hasRole('admin_sucursal') && $user->branch_id === null) {
            abort(403, 'El administrador no tiene una sucursal asignada.');
        }

        return $next($request);
    }
}
