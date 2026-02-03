<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTechnical
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si NO es técnico ni Super Admin, prohibir paso
        if (! $request->user() || ! $request->user()->isTechnical()) {
            abort(403, '⛔ Acceso denegado. Área exclusiva para Técnicos.');
        }

        return $next($request);
    }
}