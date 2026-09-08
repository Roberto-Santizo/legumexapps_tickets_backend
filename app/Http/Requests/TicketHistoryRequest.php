<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class TicketHistoryRequest extends FormRequest
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
            'ticket_id'=>['required', 'integer', 'exists:tickets,id'],
            'action'=>['required', 'string', 'max:225'],
            'old_value'=>['nullable', 'string', 'max:225'],
            'new_value'=>['nullable', 'string', 'max:225']
        ];
    }

    public function messages()
    {
        return [
            'ticket_id.required' => 'El ticket es obligatorio.',
            'ticket_id.integer' => 'El ID del ticket debe ser un entero.',
            'ticket_id.exists' => 'El ticket seleccionado no existe.',

            'action.require' => 'La acción es obligatoria.',
            'action.string' => 'La acción debe ser texto.',
            'action.max' => 'La acción no puede superar los 225 caracteres.',

            'old_value.string' => 'El valor anterior debe ser texto.',
            'old_value.max' => 'El valor anterior no puede superar los 225 caracteres.',

            'new_value.string' =>  'El nuevo valor deber ser texto.',
            'new_value.max' => 'El nuevo valor no puede superar los 225 caracteres.'
        ];
    }
}
