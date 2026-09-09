<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'=>['required', 'string', 'max:225'],
            'email'=>['required','email','max:225'],
            'password'=>['required', 'string', 'min:8'],
            'role'=>['required', Rule::enum(UserRole::class)],
        ];
    }

    public function messages(): array
    {
        return[
            'name.required' => 'El nombre es obligatorio.',
            'name.strin' => 'El nombre debe de ser una cadena de texto.',
            'name.max' => 'El nombre no puede superar los 225 caracteres.',

            'email.required' => 'El email es obligatorio',
            'email.email' => 'El correo electrónico no es válido.',
            'email.max' => 'El email no puede superar los 225 caracteres.',

            'role.required' => 'El rol es obligatorio.',
            'rol.enum' => 'El rol seleccionado no es válido.'
        ];
    }
}
