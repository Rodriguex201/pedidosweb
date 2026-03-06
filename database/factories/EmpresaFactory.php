<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'codigo' => strtoupper(fake()->bothify('?###')),
            'ip_servidor' => fake()->optional()->ipv4(),
            'database' => fake()->optional()->slug(),
            'activa' => true,
        ];
    }
}
