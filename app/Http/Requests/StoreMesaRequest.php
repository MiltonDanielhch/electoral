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
            'numero_mesa' => 'required|integer|min:1',
            'estado'      => ['required', 'string', Rule::in(['Habilitada', 'Escrutada', 'Anulada', 'Observada'])],
        ];
    }

    public function messages()
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'id_recinto.required' => 'El recinto es obligatorio.',
            'id_recinto.exists' => 'El recinto seleccionado no existe.',
            'numero_mesa.required' => 'El número de mesa es obligatorio.',
            'numero_mesa.integer' => 'El número de mesa debe ser un número entero.',
            'numero_mesa.min' => 'El número de mesa debe ser mayor o igual a 1.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
