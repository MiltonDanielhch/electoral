<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('edit_users');
    }

    public function rules()
    {
        $userId = $this->route('id');

        return [
            'status' => 'nullable|boolean',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'role_id.exists' => 'El rol seleccionado no existe.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
