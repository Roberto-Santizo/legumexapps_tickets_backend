<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Enums\UserRole;
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
        $user = auth()->user();

        $isAdmin = $user?->role === UserRole::ADMIN;
        $rules = [

            'title' => ['required','string','max:255'],

            'description' => ['required','string'],

            'category_id' => ['required','integer','exists:ticket_categories,id'],

            'priority' => ['required', Rule::enum(TicketPriority::class)],
            ];

        if ($this->isMethod('post')){
            $rules['ticket_number']=['required','integer',Rule::unique('tickets', 'ticket_number')];
        }

        if($this->isMethod('put') || $this->isMethod('patch')){
            $rules['ticket_number'] = ['prohibited'];

            if ($isAdmin){
                $rules['status'] = ['required', Rule::enum(TicketStatus::class)];

                $rules['priority'] = ['required', Rule::enum(TicketPriority::class)];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'ticket_number.required' => 'El campo de número de ticket es obligatorio.',
            'ticket_number.integer' => 'El número de ticket debe ser un entero.',
            'ticket_number.unique' => 'El número de ticket ya existe.',

            'title.required' => 'El campo de título es obligatorio.',
            'title.string' => 'El título debe ser texto.',
            'title.max' => 'El título no puede superar los 255 caracteres.',

            'description.required' => 'El campo de descripción es obligatorio.',
            'description.string' => 'La descripción debe ser texto.',
        ];
    }
}
