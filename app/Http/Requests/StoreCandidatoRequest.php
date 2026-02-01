<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidatoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre_completo' => 'required|string|max:200',
            'ci' => 'required|string|max:20|unique:candidatos',
            'imagen' => 'nullable|image|max:2048',
            'id_partido' => 'required|exists:organizaciones_politicas,id_partido',
            'id_cargo' => 'required|exists:cargos,id_cargo',
            'id_geografia_postulacion' => 'required|exists:geografias,id_geografia',
            'estado' => ['required', 'string', Rule::in(['Activo', 'Inactivo'])],
        ];
    }

    public function messages()
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'Este CI ya está en uso.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.max' => 'La imagen no debe pesar más de 2MB.',
            'id_partido.required' => 'El partido es obligatorio.',
            'id_partido.exists' => 'El partido seleccionado no existe.',
            'id_cargo.required' => 'El cargo es obligatorio.',
            'id_cargo.exists' => 'El cargo seleccionado no existe.',
            'id_geografia_postulacion.required' => 'La geografía de postulación es obligatoria.',
            'id_geografia_postulacion.exists' => 'La geografía de postulación seleccionada no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
