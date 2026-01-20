<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMesaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'codigo_tse' => 'required|string|max:20|unique:mesas',
            'id_recinto' => 'required|exists:recintos,id_recinto',
            'estado' => ['required', 'string', Rule::in(['Activa', 'Inactiva'])],
        ];
    }

    public function messages()
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'id_recinto.required' => 'El recinto es obligatorio.',
            'id_recinto.exists' => 'El recinto seleccionado no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
