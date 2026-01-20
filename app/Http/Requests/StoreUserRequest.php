<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('add_users');
    }

    public function rules()
    {
        return [
            'person_id' => 'required|exists:people,id,deleted_at,NULL,status,1',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            'person_id.required' => 'La persona es obligatoria.',
            'person_id.exists' => 'La persona seleccionada no existe o no está activa.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'El email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists' => 'El rol seleccionado no existe.',
        ];
    }
}
