<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CuadernoSeeder extends Seeder {
    /**
     * Run the migrations.
     */
    public function run(): void {
        DB::table('entrega_cuaderno')->insert([
            [
                'fecha_creacion' => now()->subDays(10)->toDateString(),
                'fecha_limite'   => now()->addDays(7)->toDateString(),
                'tutor_id'       => 1, // Debe existir en la tabla tutores
                'descripcion'    => 'Primera entrega del cuaderno de prácticas',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);

        // 2️⃣ Creamos el cuaderno entregado por el alumno
        DB::table('alumno_entrega')->insert([
            [
                'url_entrega'    => 'cuaderno_entrega_1.pdf',
                'fecha_entrega'  => now()->subDays(2)->toDateString(),
                'alumno_id'      => 1, // Debe existir en la tabla alumnos
                'entrega_id'     => 1, // ID de entrega_cuaderno creada arriba
                'observaciones'  => 'Buen trabajo general, mejorar redacción.',
                'feedback'       => 'Bien',
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);
        DB::table('notas_cuaderno')->insert([
            [
                'nota' => 8.50,
                'alumno_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas');
    }
};
