<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFilePathSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(['admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pdf_path' => ['sometimes', 'string', 'max:500'],
            'opart_total_chars' => ['nullable', 'integer', 'min:0'],
            'opart_remove_before' => ['nullable', 'integer', 'min:0'],
            'opart_remove_after' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'pdf_path.required' => 'Il percorso PDF è obbligatorio',
            'pdf_path.max' => 'Il percorso PDF non può superare i 500 caratteri',
            'opart_total_chars.integer' => 'Il numero di caratteri deve essere un numero intero',
            'opart_remove_before.integer' => 'Il numero di caratteri da rimuovere prima deve essere un numero intero',
            'opart_remove_after.integer' => 'Il numero di caratteri da rimuovere dopo deve essere un numero intero',
        ];
    }
}
