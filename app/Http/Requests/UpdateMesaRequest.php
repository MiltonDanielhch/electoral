<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMesaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $mesaId = $this->route('mesa')->id_mesa;

        return [
            'codigo_tse' => ['required', 'regex:/^[0-9]{11}$/', Rule::unique('mesas')->ignore($mesaId, 'id_mesa')],
            'id_recinto' => 'required|exists:recintos,id_recinto',
            'numero_mesa' => 'required|integer|min:1',
            'estado' => ['required', 'string', Rule::in(['Habilitada', 'Escrutada', 'Anulada', 'Observada'])],
            'cantidad_electores' => 'required|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'codigo_tse.regex' => 'El código TSE debe contener exactamente 11 dígitos numéricos.',
            'numero_mesa.required' => 'El número de mesa es obligatorio.',
            'id_recinto.required' => 'El recinto es obligatorio.',
            'id_recinto.exists' => 'El recinto seleccionado no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'cantidad_electores.required' => 'La cantidad de electores es obligatoria.',
            'cantidad_electores.integer' => 'La cantidad de electores debe ser un número entero.',
            'cantidad_electores.min' => 'La cantidad de electores debe ser mayor o igual a 0.',
        ];
    }
}
