<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkPhaseImageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'fllav' => 'required|string|max:50',
            'opart' => 'required|string|max:50',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240', // Max 10MB per immagine
        ];
    }

    /**
     * Messaggi di validazione personalizzati
     */
    public function messages(): array
    {
        return [
            'fllav.required' => 'Il codice lavorazione è obbligatorio',
            'opart.required' => 'Il codice articolo è obbligatorio',
            'images.required' => 'Devi selezionare almeno un\'immagine',
            'images.*.image' => 'Il file deve essere un\'immagine',
            'images.*.mimes' => 'Sono accettati solo formati: JPEG, JPG, PNG, GIF, WEBP',
            'images.*.max' => 'Ogni immagine non può superare i 10MB',
        ];
    }
}
