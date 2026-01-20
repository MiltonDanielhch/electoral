<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'ci' => fake()->unique()->numerify('########'),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional()->firstName(),
            'paternal_surname' => fake()->lastName(),
            'maternal_surname' => fake()->lastName(),
            'birth_date' => fake()->date(),
            'email' => fake()->boolean() ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->boolean() ? fake()->phoneNumber() : null,
            'address' => fake()->boolean() ? fake()->address() : null,
            'gender' => fake()->randomElement(['Masculino', 'Femenino']),
            'image' => null,
            'status' => fake()->randomElement([0, 1, 2]),
            'registerUser_id' => null,
            'registerRole' => null,
        ];
    }

    public function active()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 2,
        ]);
    }
}
