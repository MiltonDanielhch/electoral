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

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->status == 'on' || $this->status == '1' ? 1 : 0,
        ]);
    }

    public function rules()
    {
        $routeParam = $this->route('person') ?? $this->route('id');
        $id = is_object($routeParam) ? $routeParam->id : $routeParam;

        return [
            'tipo_doc' => 'required|string|max:10',
            'ci' => [
                'required',
                'string',
                'max:20',
                // Valida unicidad compuesta ignorando el registro actual
                Rule::unique('people')->ignore($id)->where(function ($query) {
                    return $query->where('tipo_doc', $this->tipo_doc)
                                 ->where('ci_complemento', $this->ci_complemento);
                }),
            ],
            'ci_complemento' => 'nullable|string|max:5',

            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',

            'email' => 'nullable|email|unique:people,email,' . $id . '|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'nullable|string|in:Masculino,Femenino',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
            'padron' => 'nullable|string|max:50',
            'status' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'Este documento de identidad ya está registrado (verifique tipo y complemento).',
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.max' => 'El nombre no debe superar 255 caracteres.',
            'paternal_surname.required' => 'El apellido paterno es obligatorio.',
            'paternal_surname.max' => 'El apellido paterno no debe superar 255 caracteres.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'El email ya está registrado.',
            'phone.regex' => 'El teléfono debe contener entre 7 y 20 caracteres alfanuméricos.',
            'gender.in' => 'El género debe ser Masculino o Femenino.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'image.mimes' => 'La imagen debe ser jpeg, jpg, png, bmp o webp.',
            'image.max' => 'La imagen no debe superar 10MB.',
            'status.boolean' => 'El campo estado debe ser verdadero o falso.',
        ];
    }
}
