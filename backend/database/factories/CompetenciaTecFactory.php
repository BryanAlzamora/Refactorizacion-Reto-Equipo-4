<?php

namespace Database\Factories;

use App\Models\Ciclos;
use App\Models\CompetenciaTec;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetenciaTecFactory extends Factory
{
    protected $model = CompetenciaTec::class;

    public function definition(): array
    {
        return [
            'descripcion' => $this->faker->text(),
            'ciclo_id' => Ciclos::factory()->create()->id,
        ];
    }
}
