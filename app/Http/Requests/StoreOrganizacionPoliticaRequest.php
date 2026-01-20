<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizacionPoliticaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo_tse' => 'required|string|max:3|unique:organizaciones_politicas,codigo_tse',
            'nombre' => 'required|string|max:100',
            'sigla' => 'required|string|max:10|unique:organizaciones_politicas,sigla',
            'color_hex' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'estado' => ['required', 'string', 'in:Activo,Inactivo'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_tse.required' => 'El código TSE es obligatorio.',
            'codigo_tse.max' => 'El código TSE no puede exceder 3 caracteres.',
            'codigo_tse.unique' => 'Este código TSE ya está en uso.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 100 caracteres.',
            'sigla.required' => 'La sigla es obligatoria.',
            'sigla.max' => 'La sigla no puede exceder 10 caracteres.',
            'sigla.unique' => 'Esta sigla ya está en uso.',
            'color_hex.required' => 'El color hexadecimal es obligatorio.',
            'color_hex.size' => 'El color hexadecimal debe tener exactamente 7 caracteres.',
            'color_hex.regex' => 'El color hexadecimal debe tener el formato #RRGGBB.',
            'logo_url.image' => 'El logo debe ser una imagen.',
            'logo_url.mimes' => 'El logo debe ser de tipo: jpeg, png, jpg o gif.',
            'logo_url.max' => 'El logo no puede exceder 2MB.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser Activo o Inactivo.',
        ];
    }
}
