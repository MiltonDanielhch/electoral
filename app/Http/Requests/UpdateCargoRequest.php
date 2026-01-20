<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCargoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'descripcion' => 'required|string|max:60',
            'nivel' => ['required', 'string', 'in:D,P,M'],
            'tipo_acta' => ['required', 'string', 'in:Normal,Especial'],
            'acta_unica' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede exceder 60 caracteres.',
            'nivel.required' => 'El nivel es obligatorio.',
            'nivel.in' => 'El nivel debe ser D, P o M.',
            'tipo_acta.required' => 'El tipo de acta es obligatorio.',
            'tipo_acta.in' => 'El tipo de acta debe ser Normal o Especial.',
            'acta_unica.required' => 'El campo acta única es obligatorio.',
            'acta_unica.boolean' => 'El campo acta única debe ser verdadero o falso.',
        ];
    }
}
