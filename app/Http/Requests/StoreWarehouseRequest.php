<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'warehouse_position' => ['required_if:pending,false', 'nullable', 'string', 'max:50'],
            'product_code' => ['required', 'string', 'max:100'],
            'product_description' => ['nullable', 'string', 'max:500'],
            'production_order' => ['nullable', 'string', 'max:100'],
            'pending' => ['boolean'],
            'pending_code' => ['required_if:pending,true', 'nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'warehouse_position.required_if' => 'La posizione è obbligatoria per articoli non in attesa',
            'product_code.required' => 'Il codice prodotto è obbligatorio',
            'pending_code.required_if' => 'Il codice di attesa è obbligatorio per articoli in attesa',
            'quantity.min' => 'La quantità non può essere negativa'
        ];
    }
}
