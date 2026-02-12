<?php

namespace Database\Factories;

use App\Models\Asignatura;
use App\Models\Ciclos;
use App\Models\CompetenciaTec;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignaturaFactory extends Factory
{
    protected $model = Asignatura::class;

    public function definition(): array
    {
        return [
            'codigo_asignatura' => $this->faker->text(),
            'ciclo_id' => Ciclos::factory()->create()->id,
            'nombre_asignatura' => $this->faker->text()
        ];
    }
}
