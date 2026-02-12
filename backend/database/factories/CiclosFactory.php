<?php

namespace Database\Factories;

use App\Models\Ciclos;
use App\Models\FamiliaProfesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class CiclosFactory extends Factory
{
    protected $model = Ciclos::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'familia_profesional_id' => FamiliaProfesional::factory(),
            'grupo' => $this->faker->unique()->numerify('GR-##'),
        ];
    }
}
