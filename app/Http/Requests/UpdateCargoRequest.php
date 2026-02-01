<?php

namespace App\Http\Requests;

class UpdateCargoRequest extends CargoRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('edit_cargos');
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['descripcion'] = 'required|string|max:60|unique:cargos,descripcion,' . $this->cargo->id_cargo . ',id_cargo';
        return $rules;
    }
}
