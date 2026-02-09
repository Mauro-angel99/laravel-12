<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignWorkPhaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('assign-work-phases');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['required', 'integer'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'selected.required' => 'Seleziona almeno una fase di lavoro',
            'selected.min' => 'Seleziona almeno una fase di lavoro',
            'selected.*.integer' => 'ID fase non valido',
            'assigned_to.required' => 'Seleziona un utente a cui assegnare',
            'assigned_to.exists' => 'L\'utente selezionato non esiste',
            'notes.max' => 'Le note non possono superare i 1000 caratteri'
        ];
    }
}
