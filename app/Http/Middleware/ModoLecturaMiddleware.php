<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware de "Modo Lectura" para Elecciones
 * 
 * MEJORA DE SEGURIDAD: Bloquea las rutas de escritura (store, update, destroy)
 * cuando el proceso electoral entra en fase de votación o está bloqueado.
 * 
 * Configuración requerida en .env:
 * ELECCIONES_BLOQUEADAS=true
 */
class ModoLecturaMiddleware
{
    /**
     * Rutas protegidas por este middleware (solo lectura cuando está activo)
     *
     * @var array
     */
    protected $rutasProtegidas = [
        'admin.candidatos.store',
        'admin.candidatos.update',
        'admin.candidatos.destroy',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si las elecciones están bloqueadas
        if (config('elecciones.bloqueadas', false)) {
            $rutaActual = $request->route()->getName();
            
            // Si la ruta actual está protegida y es un método de escritura
            if (in_array($rutaActual, $this->rutasProtegidas)) {
                return redirect()
                    ->route('admin.candidatos.index')
                    ->with([
                        'message' => 'El sistema está en modo lectura. No se permiten modificaciones durante la fase de votación.',
                        'alert-type' => 'warning'
                    ]);
            }
        }

        return $next($request);
    }
}