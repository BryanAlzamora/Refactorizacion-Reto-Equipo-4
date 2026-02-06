<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\CompetenciaTec;
use App\Models\ResultadoAprendizaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetenciaRaController extends Controller
{
    /**
     * Obtiene las competencias técnicas de un ciclo y las asignaturas con sus RAs.
     */
    public function getCompRa($cicloId)
    {
        // 1. Obtener Competencias Técnicas asociadas al Ciclo
        // Basado en: 2026_01_13_082408_create_competencias_tec_table.php
        $competencias = CompetenciaTec::where('ciclo_id', $cicloId)->get();

        // 2. Obtener Asignaturas del Ciclo con sus RAs y las relaciones pivote
        // Asumimos que Asignatura tiene 'ciclo_id' o una relación para filtrar por ciclo.
        $asignaturas = Asignatura::where('ciclo_id', $cicloId)
            ->with([
                // Cargamos los Resultados de Aprendizaje (antiguos RAs)
                'resultadosAprendizaje' => function ($query) {
                    $query->with([
                        // Cargamos la relación Many-to-Many con CompetenciasTec
                        // Esto consulta la tabla pivote 'competencias_tec_ra'
                        'competenciasTec' => function ($q) {
                            $q->select('competencias_tec.id'); 
                        }
                    ]);
                }
            ])
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'competencias' => $competencias,
            'asignaturas' => $asignaturas
        ]);
    }

    /**
     * Crea o elimina la relación entre una Competencia Técnica y un Resultado de Aprendizaje.
     */
    public function createOrDelete(Request $req)
    {
        // Validación actualizada a los nuevos nombres de columna
        $data = $req->validate([
            'competencia_tec_id'       => 'required|integer|exists:competencias_tec,id',
            'resultado_aprendizaje_id' => 'required|integer|exists:resultados_aprendizaje,id',
            // Nota: Ya no necesitamos 'ID_Asignatura' en el pivote
        ]);

        // Usamos DB::table directamente para manejar el pivote 'competencias_tec_ra'
        // ya que es una tabla intermedia pura sin modelo propio usualmente.
        $existing = DB::table('competencias_tec_ra')
            ->where('competencia_tec_id', $data['competencia_tec_id'])
            ->where('resultado_aprendizaje_id', $data['resultado_aprendizaje_id'])
            ->first();

        if ($existing) {
            // Eliminar relación
            DB::table('competencias_tec_ra')
                ->where('competencia_tec_id', $data['competencia_tec_id'])
                ->where('resultado_aprendizaje_id', $data['resultado_aprendizaje_id'])
                ->delete();

            return response()->json([
                'action' => 'deleted',
                'message' => 'Relación eliminada correctamente'
            ]);
        }

        // Crear relación
        // Basado en: 2026_01_13_082422_create_competencias_tec_ra_table.php
        // (Usa timestamps, así que añadimos created_at y updated_at)
        DB::table('competencias_tec_ra')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now()
        ]));

        return response()->json([
            'action' => 'created',
            'message' => 'Relación creada correctamente',
            'data' => $data
        ]);
    }
}