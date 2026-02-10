<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetenciasTecRaSeeder extends Seeder {
    public function run() {
        $ciclo_id = 1;

        // Obtener todas las competencias técnicas del ciclo DAW ordenadas por ID
        $competencias = DB::table('competencias_tec')
            ->where('ciclo_id', $ciclo_id)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $relacionesCompetenciasRA = [
            'SI' => [
                1 => [7],
                2 => [7],
                3 => [7],
                4 => [7],
                5 => [5],
                6 => [7],
                7 => [7],
            ],
            'BD' => [
                1 => [4],
                2 => [4],
                3 => [4],
                4 => [4],
                5 => [4],
                6 => [4],
                7 => [4],
                8 => [4, 5],
            ],
            'PRO' => [
                1 => [1, 3],
                2 => [1],
                3 => [1, 9],
                4 => [1],
                5 => [1],
                6 => [1],
                7 => [1],
                8 => [1],
                9 => [1, 3],
            ],
            'LMSGI' => [
                1 => [2],
                2 => [2],
                3 => [2],
                4 => [2, 6],
                5 => [2, 6],
                6 => [2, 6],
                7 => [4],
            ],
            'ED' => [
                1 => [1, 8],
                2 => [1],
                3 => [1, 9],
                4 => [1, 9],
                5 => [1],
                6 => [1],
            ],
            'DWEC' => [
                1 => [2],
                2 => [2],
                3 => [2],
                4 => [2],
                5 => [2],
                6 => [2],
                7 => [2, 6],
            ],
            'DWES' => [
                1 => [3],
                2 => [3],
                3 => [3],
                4 => [3],
                5 => [3],
                6 => [3, 5],
                7 => [3, 6],
                8 => [3],
                9 => [3, 6],
            ],
            'DAW' => [
                1 => [7],
                2 => [5, 7],
                3 => [7, 9],
                4 => [7],
                5 => [7],
                6 => [7, 8],
            ],
            'DIW' => [
                1 => [2],
                2 => [2],
                3 => [2],
                4 => [2],
                5 => [2],
                6 => [2],
            ],
        ];

        foreach ($relacionesCompetenciasRA as $codigoAsignatura => $resultados) {
            // Obtener todos los RAs de cada asignatura
            $asignaturaId = DB::table('asignaturas')
                ->where('codigo_asignatura', $codigoAsignatura)
                ->value('id');

            if (!$asignaturaId) continue;

            $ras = DB::table('resultados_aprendizaje')
                ->where('asignatura_id', $asignaturaId)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            // Insertar los RA
            foreach ($resultados as $numeroRA => $posicionesCompetencias) {
                $raId = $ras[$numeroRA - 1] ?? null;

                if (!$raId) continue;

                foreach ($posicionesCompetencias as $posicionCompetencia) {
                    $competenciaId = $competencias[$posicionCompetencia - 1] ?? null;

                    if (!$competenciaId) continue;

                    DB::table('competencia_tec_ra')->insert([
                        'competencia_tec_id' => $competenciaId,
                        'resultado_aprendizaje_id' => $raId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
