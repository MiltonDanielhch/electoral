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
            'person_type' => 'required|in:Natural,Jurídica',
            'tipo_doc' => 'required|string|max:10',
            'ci' => [
                'nullable',
                'required_if:person_type,Natural',
                'string',
                'max:20',
                // Valida unicidad compuesta ignorando el registro actual
                Rule::unique('people')->ignore($id)->where(function ($query) {
                    return $query->where('tipo_doc', $this->tipo_doc)
                                 ->where('ci_complemento', $this->ci_complemento);
                }),
            ],
            'ci_complemento' => 'nullable|string|max:5',
            'nit' => 'nullable|required_if:person_type,Jurídica|string|max:20|unique:people,nit,' . $id,

            'first_name' => 'nullable|required_if:person_type,Natural|string|max:255',
            'paternal_surname' => 'nullable|required_if:person_type,Natural|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'legal_name' => 'nullable|required_if:person_type,Jurídica|string|max:255',

            'email' => 'nullable|email|unique:people,email,' . $id . '|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'nullable|string|in:Masculino,Femenino',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
            'padron' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'person_type.required' => 'El tipo de persona es obligatorio.',
            'person_type.in' => 'El tipo de persona debe ser Natural o Jurídica.',
            'ci.required_if' => 'El CI es obligatorio para personas naturales.',
            'ci.unique' => 'Este documento de identidad ya está registrado (verifique tipo y complemento).',
            'nit.required_if' => 'El NIT es obligatorio para personas jurídicas.',
            'nit.unique' => 'El NIT ya está registrado.',
            'first_name.required_if' => 'El nombre es obligatorio para personas naturales.',
            'first_name.max' => 'El nombre no debe superar 255 caracteres.',
            'paternal_surname.required_if' => 'El apellido paterno es obligatorio para personas naturales.',
            'paternal_surname.max' => 'El apellido paterno no debe superar 255 caracteres.',
            'legal_name.required_if' => 'La razón social es obligatoria para personas jurídicas.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'El email ya está registrado.',
            'phone.regex' => 'El teléfono debe contener entre 7 y 20 caracteres alfanuméricos.',
            'gender.in' => 'El género debe ser Masculino o Femenino.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'image.mimes' => 'La imagen debe ser jpeg, jpg, png, bmp o webp.',
            'image.max' => 'La imagen no debe superar 10MB.',
        ];
    }
}
