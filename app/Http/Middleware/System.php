<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class System
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Rutas críticas siempre abiertas
        $open = [
            'admin/login',
            'admin/logout',
            'admin/password/*',
            'admin/voyager-assets*',
            '/',
        ];
        if ($request->is($open)) {
            return $next($request);
        }

        // 2. Modo mantenimiento
        if (setting('configuracion.maintenance') === '1') {
            if (auth()->check() && auth()->user()->hasRole(['admin', 'Administrador'])) {
                return $next($request);
            }
            return response()->view('errors.503', [], 503);
        }

        // 3. Desarrollo: solo admins
        if (Auth::user()) {
            if (setting('system.development') && !auth()->user()->hasRole('admin')) {
               return response()->view('errors.503', [], 503);
            }
        }

        // 4. Si todo está bien, continuar
        return $next($request);
    }
}
