<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreActaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Preparamos los datos antes de validar.
     * Esto convierte el texto JSON de Insomnia en un Array real.
     */
    protected function prepareForValidation()
    {
        if (is_string($this->votos_partido)) {
            $this->merge([
                'votos_partido' => json_decode($this->votos_partido, true),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'codigo_mesa' => 'required|string|size:11|exists:mesas,codigo_tse',
            'id_cargo' => 'required|integer|exists:cargos,id_cargo',
            'codigo_acta' => 'required|string|max:30|unique:actas_escrutinio,codigo_acta',
            'foto_frontal' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'foto_reverso' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'total_sobres' => 'required|integer|min:0',
            'total_votantes' => 'required|integer|min:0',
            'votos_validos' => 'required|integer|min:0',
            'votos_blancos' => 'required|integer|min:0',
            'votos_nulos' => 'required|integer|min:0',
            'votos_impugnados' => 'nullable|integer|min:0',
            'digitador' => 'required|string|max:30',
            'votos_partido' => 'required|array|min:1',
            'votos_partido.*.id_partido' => 'required|integer|exists:organizaciones_politicas,id_partido',
            'votos_partido.*.votos' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_mesa.required' => 'El código de la mesa es obligatorio',
            'codigo_mesa.size' => 'El código de la mesa debe tener 11 caracteres',
            'codigo_mesa.exists' => 'La mesa especificada no existe',
            'id_cargo.required' => 'El cargo es obligatorio',
            'id_cargo.exists' => 'El cargo especificado no existe',
            'codigo_acta.required' => 'El código del acta es obligatorio',
            'codigo_acta.unique' => 'El código del acta ya existe',
            'foto_frontal.image' => 'La foto frontal debe ser una imagen válida',
            'foto_frontal.mimes' => 'La foto frontal debe ser JPEG, PNG o JPG',
            'foto_frontal.max' => 'La foto frontal no puede superar 5MB',
            'foto_reverso.image' => 'La foto reverso debe ser una imagen válida',
            'foto_reverso.mimes' => 'La foto reverso debe ser JPEG, PNG o JPG',
            'foto_reverso.max' => 'La foto reverso no puede superar 5MB',
            'votos_partido.required' => 'Los votos por partido son obligatorios',
            'votos_partido.array' => 'Los votos por partido deben ser un arreglo',
            'votos_partido.*.id_partido.required' => 'El ID del partido es obligatorio',
            'votos_partido.*.id_partido.exists' => 'El partido especificado no existe',
            'votos_partido.*.votos.required' => 'Los votos son obligatorios',
            'votos_partido.*.votos.min' => 'Los votos no pueden ser negativos',
        ];
    }
}
