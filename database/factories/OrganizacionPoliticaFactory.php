<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizacionPolitica>
 */
class OrganizacionPoliticaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $codigo = fake()->unique()->lexify('???');

        return [
            'codigo_tse' => strtoupper(substr($codigo, 0, 3)),
            'nombre' => fake()->company() . ' de Bolivia',
            'sigla' => strtoupper($codigo),
            'color_hex' => '#' . str_pad(dechex(fake()->numberBetween(0, 16777215)), 6, '0', STR_PAD_LEFT),
            'logo_url' => null,
            'estado' => 'Activo',
        ];
    }
}
