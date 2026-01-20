<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cargo>
 */
class CargoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'descripcion' => fake()->randomElement(['Gobernador', 'Alcalde', 'Concejal', 'Asambleísta']),
            'nivel' => fake()->randomElement(['D', 'P', 'M']),
            'tipo_acta' => 'Normal',
            'acta_unica' => true,
        ];
    }
}
