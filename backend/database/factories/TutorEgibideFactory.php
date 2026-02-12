<?php

namespace Database\Factories;

use App\Models\TutorEgibide;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TutorEgibideFactory extends Factory
{
    protected $model = TutorEgibide::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nombre' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'telefono' => $this->faker->phoneNumber(),
            'ciudad' => $this->faker->city(),
            'alias' => $this->faker->userName(),
        ];
    }
}
