<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CicloSeeder extends Seeder {
 public function run(): void {
    DB::table('ciclos')->insert([
        [
            'nombre' => 'Desarrollo de Aplicaciones Web',
            'grupo' => '142GA', // Código que aparece en tus archivos CSV
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'nombre' => 'Electromecánica de Vehículos',
            'grupo' => '131DA', // Otro ejemplo de tu CSV
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
}
}
