<?php

namespace Database\Factories;

use App\Models\FilePathSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FilePathSetting>
 */
class FilePathSettingFactory extends Factory
{
    protected $model = FilePathSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pdf_path' => 'C:\\PDFs\\' . fake()->word(),
            'image_path' => 'C:\\Images\\' . fake()->word(),
        ];
    }
}
