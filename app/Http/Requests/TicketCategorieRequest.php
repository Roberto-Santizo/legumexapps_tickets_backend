<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255',Rule::unique('ticket_categories', 'name')->ignore($this->route('id')),],
            'description' => ['nullable','string',],
            'active' => ['sometimes','boolean',],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string' => 'El nombre de la categoría debe ser texto.',
            'name.max' => 'El nombre de la categoría no puede superar los 255 caracteres.',
            'name.unique' => 'La categoría ya existe.',

            'description.string' => 'La descripción debe ser texto.',
        ];
    }
}