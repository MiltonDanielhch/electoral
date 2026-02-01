<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecintoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'codigo_tse' => 'required|string|size:3|regex:/^[0-9]+$/|unique:recintos',
            'id_geografia' => 'required|exists:geografias,id_geografia',
            'nombre' => 'required|string|max:150',
            'direccion' => 'nullable|string|max:255',
            'latitud' => [
                'nullable',
                'numeric',
                'between:-90,90',
                // Sintonía: Geofencing - Validar coordenadas dentro del Departamento del Beni
                function ($attribute, $value, $fail) {
                    if ($value !== null && ($value < -16.5 || $value > -10.0)) {
                        $fail('La latitud debe estar dentro del Departamento del Beni (entre -16.50 y -10.00).');
                    }
                },
            ],
            'longitud' => [
                'nullable',
                'numeric',
                'between:-180,180',
                // Sintonía: Geofencing - Validar coordenadas dentro del Departamento del Beni
                function ($attribute, $value, $fail) {
                    if ($value !== null && ($value < -68.0 || $value > -60.0)) {
                        $fail('La longitud debe estar dentro del Departamento del Beni (entre -68.00 y -60.00).');
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.size' => 'El código TSE debe ser exactamente 3 dígitos.',
            'codigo_tse.regex' => 'El código TSE debe contener solo números.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'id_geografia.required' => 'La geografía es obligatoria.',
            'id_geografia.exists' => 'La geografía seleccionada no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'latitud.between' => 'La latitud debe estar entre -90 y 90.',
            'longitud.between' => 'La longitud debe estar entre -180 y 180.',
        ];
    }
}
