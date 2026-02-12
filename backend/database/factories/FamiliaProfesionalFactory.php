<?php

namespace Database\Factories;

use App\Models\FamiliaProfesional;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamiliaProfesionalFactory extends Factory
{
    protected $model = FamiliaProfesional::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Informática y Comunicaciones',
                'Electricidad y Electrónica',
                'Administración y Gestión',
                'Fabricación Mecánica',
                'Transporte y Mantenimiento de Vehículos'
            ]),
            'codigo_familia' => $this->faker->unique()->numerify('FP-###'),
        ];
    }
}
