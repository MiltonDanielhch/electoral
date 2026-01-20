<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geografia>
 */
class GeografiaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = fake()->randomElement(['Departamento', 'Provincia', 'Municipio', 'Cantón', 'Localidad']);

        return [
            'codigo_tse' => str_pad(fake()->unique()->randomNumber(6), 9, '0', STR_PAD_LEFT),
            'nombre' => fake()->city(),
            'tipo' => $tipo,
            'parent_id' => null,
        ];
    }
}
