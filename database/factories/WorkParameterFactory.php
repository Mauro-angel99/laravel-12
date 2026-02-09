<?php

namespace Database\Factories;

use App\Models\WorkParameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkParameter>
 */
class WorkParameterFactory extends Factory
{
    protected $model = WorkParameter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'fields' => fake()->randomElements(
                ['campo1', 'campo2', 'campo3', 'campo4', 'campo5'],
                fake()->numberBetween(0, 5)
            ),
        ];
    }

    /**
     * Indicate that the work parameter has no fields.
     */
    public function withoutFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'fields' => [],
        ]);
    }

    /**
     * Indicate that the work parameter has specific fields.
     */
    public function withFields(array $fields): static
    {
        return $this->state(fn (array $attributes) => [
            'fields' => $fields,
        ]);
    }
}
