<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeografiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo_tse' => 'required|string|max:255|unique:geografias,codigo_tse,' . $this->route('geografia'),
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:Departamento,Provincia,Municipio',
            'parent_id' => [
                'nullable',
                'exists:geografias,id_geografia',
                // MEJORA: Impedir que el padre sea el mismo ID (solo en edición)
                function ($attribute, $value, $fail) {
                    if ($value == $this->route('geografia')) {
                        $fail('Un territorio no puede ser su propio padre.');
                    }
                },
            ],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.unique' => 'El código TSE ya está en uso.',
            'nombre.required' => 'El nombre del territorio es obligatorio.',
            'tipo.required' => 'El tipo de territorio es obligatorio.',
            'tipo.in' => 'El tipo debe ser Departamento, Provincia o Municipio.',
            'parent_id.exists' => 'La ubicación superior seleccionada no existe.',
            'latitud.between' => 'La latitud debe estar entre -90 y 90 grados.',
            'longitud.between' => 'La longitud debe estar entre -180 y 180 grados.',
        ];
    }
}
