<?php

namespace Database\Factories;

use App\Models\Empresas;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresasFactory extends Factory
{
    protected $model = Empresas::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'cif' => $this->faker->unique()->numerify('B########'),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'direccion' => $this->faker->address(),
        ];
    }
}
