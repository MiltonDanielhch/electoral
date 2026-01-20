<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recinto>
 */
class RecintoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo_tse' => str_pad(fake()->unique()->randomNumber(2), 3, '0', STR_PAD_LEFT),
            'id_geografia' => \App\Models\Geografia::factory(),
            'nombre' => fake()->company() . ' ' . fake()->randomNumber(2),
            'direccion' => fake()->streetAddress(),
        ];
    }

    public function municipio()
    {
        return $this->state([
            'id_geografia' => \App\Models\Geografia::factory()->state(['tipo' => 'Municipio']),
        ]);
    }
}
