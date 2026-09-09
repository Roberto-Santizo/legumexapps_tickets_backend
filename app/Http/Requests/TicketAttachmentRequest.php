<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class TicketAttachmentRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.required' => 'El ticket es obligatorio',
            'ticket_id.integer' => 'El ID de ticket debe de ser un número entero',
            'ticket_id.exists' => 'El ticket seleccionado no existe',

            'file.required' => 'Debe de seleccionar un archivo',
            'file.file' => 'El archivo seleccionado no es válido',
            'file.max' => 'El archivo no puede superar los 10 MB',
        ];
    }
}
