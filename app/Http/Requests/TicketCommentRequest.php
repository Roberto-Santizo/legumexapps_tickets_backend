<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketCommentRequest extends FormRequest
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
            'ticket_id' => ['required','integer','exists:tickets,id',],
            'description' => ['required','string',],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.required' => 'El ticket es obligatorio.',
            'ticket_id.integer' => 'El ID del ticket debe ser un entero.',
            'ticket_id.exists' => 'El ticket seleccionado no existe.',
            'description.required' => 'La descripción del comentario es obligatoria.',
            'description.string' => 'La descripción debe ser texto.',
        ];
    }
}
