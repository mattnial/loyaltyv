<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsBilling
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si NO es facturación ni Super Admin, prohibir paso
        if (! $request->user() || ! $request->user()->isBilling()) {
            abort(403, '⛔ Acceso denegado. Área exclusiva para Facturación.');
        }

        return $next($request);
    }
}