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
            'pdf_path' => ['required', 'string', 'max:500'],
            'image_path' => ['required', 'string', 'max:500']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pdf_path.required' => 'Il percorso PDF è obbligatorio',
            'pdf_path.max' => 'Il percorso PDF non può superare i 500 caratteri',
            'image_path.required' => 'Il percorso immagini è obbligatorio',
            'image_path.max' => 'Il percorso immagini non può superare i 500 caratteri'
        ];
    }
}
