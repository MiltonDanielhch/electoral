<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize()
    {
        // return auth()->user()->can('edit_people');
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'tipo_doc' => 'required|string|max:10|in:CI,Pasaporte',
            'ci' => [
                'required',
                'string',
                'max:20',
                // Valida unicidad ignorando el registro actual
                Rule::unique('people')->ignore($id)->where(function ($query) {
                    return $query->where('ci_complemento', $this->ci_complemento);
                }),
            ],
            'ci_complemento' => 'nullable|string|max:5',

            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',

            'email' => 'nullable|email|unique:people,email,' . $id . '|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'required|string|in:Masculino,Femenino',
            'birth_date' => 'required|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'padron' => 'nullable|string|max:50',
            'status' => 'boolean',
            'remove_image' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'tipo_doc.required' => 'El tipo de documento es obligatorio.',
            'tipo_doc.in' => 'El tipo de documento debe ser CI o Pasaporte.',
            'ci.required' => 'El número de documento es obligatorio.',
            'ci.unique' => 'Este documento de identidad ya está registrado (verifique el complemento).',
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.max' => 'El nombre no debe superar 255 caracteres.',
            'paternal_surname.required' => 'El apellido paterno es obligatorio.',
            'paternal_surname.max' => 'El apellido paterno no debe superar 255 caracteres.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'El email ya está registrado.',
            'phone.regex' => 'El teléfono debe contener entre 7 y 20 caracteres numéricos.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género debe ser Masculino o Femenino.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'image.mimes' => 'La imagen debe ser JPG o PNG.',
            'image.max' => 'La imagen no debe superar 2MB.',
        ];
    }
}
