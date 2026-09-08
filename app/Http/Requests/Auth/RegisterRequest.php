<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo de nombre es obligatorio.',
            'email.required' => 'El campo de correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'email.unique' => 'El correo ya se encuentra registrado.',
            'password.required' => 'El campo de contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'role.required' => 'El campo de rol es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
        ];
    }
}
