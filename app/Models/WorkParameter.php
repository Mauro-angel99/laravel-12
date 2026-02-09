<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkParameter extends Model
{
    protected $fillable = [
        'name',
        'fields',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    /**
     * Get validation rules for the model.
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:work_parameters,name'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string', 'max:255']
        ];
    }
}
