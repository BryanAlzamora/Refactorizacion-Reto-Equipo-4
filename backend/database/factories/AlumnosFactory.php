<?php

namespace Database\Factories;

use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\User;
use App\Models\TutorEgibide;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlumnosFactory extends Factory
{
    protected $model = Alumnos::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nombre' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'dni' => $this->faker->unique()->numerify('########') . $this->faker->randomLetter(),
            'telefono' => $this->faker->phoneNumber(),
            'ciudad' => $this->faker->city(),
            'grupo' => Ciclos::factory()->create()->grupo,
            'tutor_id' => null, // Optional, can be set later
            'matricula_id' => $this->faker->numerify('MAT-####'),
        ];
    }
}
