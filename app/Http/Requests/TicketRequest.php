<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:225', Rule::unique('tickets', 'name')->ignore($this->route('id'))],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El campo de nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
        ];
    }
}
