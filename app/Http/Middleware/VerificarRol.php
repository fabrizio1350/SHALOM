<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verificar si el usuario está activo
        if (auth()->user()->estado !== 'activo') {
            abort(403, 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        // Verificar si el rol del usuario está permitido
        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}