<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkParameterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_parameters', 'name')->ignore($this->route('work_parameter'))
            ],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string', 'max:255']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del parametro è obbligatorio',
            'name.unique' => 'Esiste già un parametro con questo nome',
            'name.max' => 'Il nome non può superare i 255 caratteri',
            'fields.array' => 'I campi devono essere un array',
            'fields.*.string' => 'Ogni campo deve essere una stringa',
            'fields.*.max' => 'Un campo non può superare i 255 caratteri'
        ];
    }
}
