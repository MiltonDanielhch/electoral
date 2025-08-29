<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use DateTime;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function custom_authorize($permission){
        if(!Auth::user()->hasPermission($permission)){
            abort(403, 'THIS ACTIO UNAUTHORIZED.');
        }
    }

    public function payment_alert()
    {
        $controller = new SolucionDigitalController();
        $data = $controller->settings_code();

        if (!$data || !isset($data->finish, $data->type)) {
            return null;
        }

        $date = $data->finish;
        $now = new DateTime();
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' 23:59:59');

        // Si es Demo, no hay restricción
        if ($data->type === 'Demo') {
            return null;
        }

        // Fecha inválida
        if (!$d || $d->format('Y-m-d') !== $date) {
            return null;
        }

        // Fecha vencida
        if ($now > $d) {
            return 'finalizado';
        }

        // Días restantes si faltan 3 o menos
        $difference = $now->diff($d);
        return $difference->days <= 3 ? $difference->days : 'vigente';
    }
}
