<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->can('add_people');
    }

    public function rules()
    {
        return [
            'ci' => 'required|string|regex:/^[0-9]{7,10}$/|unique:people,ci',
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:people,email|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'nullable|string|in:Masculino,Femenino',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
        ];
    }

    public function messages()
    {
        return [
            'ci.required' => 'El CI es obligatorio.',
            'ci.regex' => 'El CI debe contener entre 7 y 10 dígitos.',
            'ci.unique' => 'El CI ya está registrado.',
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
        ];
    }
}
