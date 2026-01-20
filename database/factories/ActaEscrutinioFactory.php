<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActaEscrutinio>
 */
class ActaEscrutinioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSobres = fake()->numberBetween(50, 500);
        $votosValidos = fake()->numberBetween(30, $totalSobres - 20);
        $votosBlancos = fake()->numberBetween(5, 10);
        $votosNulos = fake()->numberBetween(5, 10);
        $votosImpugnados = $totalSobres - ($votosValidos + $votosBlancos + $votosNulos);

        return [
            'id_mesa' => \App\Models\Mesa::factory(),
            'id_cargo' => \App\Models\Cargo::factory(),
            'codigo_acta' => 'ACTA-' . fake()->unique()->randomNumber(6),
            'foto_frontal' => null,
            'foto_reverso' => null,
            'total_sobres' => $totalSobres,
            'total_votantes' => fake()->numberBetween(40, $totalSobres),
            'votos_validos' => $votosValidos,
            'votos_blancos' => $votosBlancos,
            'votos_nulos' => $votosNulos,
            'votos_impugnados' => max(0, $votosImpugnados),
            'digitador' => fake()->name(),
            'estado' => fake()->randomElement(['Pendiente', 'Digitada', 'Observada', 'Validada', 'Cerrada']),
        ];
    }
}
