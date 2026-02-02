<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        $userToUpdate = User::findOrFail($this->route('id'));
        return $this->user()->can('update', $userToUpdate);
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
