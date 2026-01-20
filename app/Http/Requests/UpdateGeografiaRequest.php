<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeografiaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $geografiaId = $this->route('geografia')->id_geografia;

        return [
            'codigo_tse' => ['required', 'string', 'max:9', Rule::unique('geografias', 'codigo_tse')->ignore($geografiaId, 'id_geografia')],
            'nombre' => 'required|string|max:100',
            'tipo' => ['required', 'string', 'in:Departamento,Provincia,Municipio,Cantón,Localidad'],
            'parent_id' => 'nullable|exists:geografias,id_geografia',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.max' => 'El código TSE no puede exceder 9 caracteres.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser Departamento, Provincia, Municipio, Cantón o Localidad.',
            'parent_id.exists' => 'La geografía padre seleccionada no existe.',
        ];
    }
}
