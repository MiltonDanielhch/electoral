<?php

namespace App\Http\Requests;

class StoreCargoRequest extends CargoRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasPermission('add_cargos');
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['descripcion'] = 'required|string|max:60|unique:cargos,descripcion';
        return $rules;
    }
}
