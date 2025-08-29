<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SolucionDigitalController;
use App\Http\Controllers\Controller;

class System
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()) {
            if (setting('system.development') && !auth()->user()->hasRole('admin')) {
                return redirect()->route('errors', ['id' => 503]);
            }
        }

        // Intentar obtener datos de solucionDigital (opcional)
        $controller = new SolucionDigitalController();
        $data = $controller->settings_code();

        // Si hay datos, aplicar lógica de pago
        if ($data) {
            $payment = new Controller();
            if ($payment->payment_alert() === 'finalizado') {
                $blockedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
                $allowedRoutes = ['admin/login', 'admin/logout', 'admin/settings'];

                if (
                    in_array($request->method(), $blockedMethods) &&
                    !in_array($request->path(), $allowedRoutes)
                ) {
                    return redirect()->back()
                        ->withInput()
                        ->with([
                            'message' => 'Para continuar con el servicio sin interrupciones, contacte al administrador.',
                            'alert-type' => 'error'
                        ]);
                }
            }
        }

        // Si no hay datos, simplemente continúa sin bloquear
        return $next($request);
    }
}
