<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecintoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $recintoId = $this->route('recinto')->id_recinto;

        return [
            'codigo_tse' => ['required', 'string', 'max:20', Rule::unique('recintos')->ignore($recintoId, 'id_recinto')],
            'id_geografia' => 'required|exists:geografias,id_geografia',
            'nombre' => 'required|string|max:200',
            'direccion' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'id_geografia.required' => 'La geografía es obligatoria.',
            'id_geografia.exists' => 'La geografía seleccionada no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'direccion.max' => 'La dirección no puede exceder los 500 caracteres.',
        ];
    }
}
