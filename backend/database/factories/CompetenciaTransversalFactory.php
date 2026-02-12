<?php

namespace Database\Factories;

use App\Models\Ciclos;
use App\Models\CompetenciaTec;
use App\Models\CompetenciaTransversal;
use App\Models\FamiliaProfesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetenciaTransversalFactory extends Factory
{
    protected $model = CompetenciaTransversal::class;

    public function definition(): array
    {
        return [
            'descripcion' => $this->faker->text(),
            'nivel' => $this->faker->numberBetween(1,4),
            'familia_profesional_id' => FamiliaProfesional::factory()->create()->id
        ];
    }
}
