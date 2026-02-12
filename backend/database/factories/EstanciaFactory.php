<?php

namespace Database\Factories;

use App\Models\Estancia;
use App\Models\Alumnos;
use App\Models\Empresas;
use App\Models\TutorEgibide;
use App\Models\TutorEmpresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstanciaFactory extends Factory
{
    protected $model = Estancia::class;

    public function definition(): array
    {
        $fechaInicio = $this->faker->dateTimeBetween('-1 year', 'now');
        $fechaFin = $this->faker->dateTimeBetween($fechaInicio, '+6 months');

        return [
            'alumno_id' => Alumnos::factory(),
            'empresa_id' => Empresas::factory(),
            'instructor_id' => TutorEmpresa::factory(),
            'puesto' => $this->faker->jobTitle(),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'horas_totales' => $this->faker->numberBetween(300, 600),
        ];
    }
}
