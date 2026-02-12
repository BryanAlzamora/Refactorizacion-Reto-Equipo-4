<?php

namespace Database\Factories;

use App\Models\TutorEmpresa;
use App\Models\User;
use App\Models\Empresas;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorEmpresaFactory extends Factory
{
    protected $model = TutorEmpresa::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'empresa_id' => Empresas::factory(),
            'nombre' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'telefono' => $this->faker->phoneNumber(),
            'ciudad' => $this->faker->city(),
        ];
    }
}
