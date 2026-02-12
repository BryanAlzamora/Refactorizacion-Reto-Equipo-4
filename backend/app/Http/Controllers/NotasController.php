<?php

namespace App\Http\Controllers;

use App\Models\CuadernoPracticas;
use App\Models\Estancia;
use App\Models\NotaAsignatura;
use App\Models\NotaCuaderno;
use App\Services\CalcularNotasCompetenciasTecnicas;
use App\Services\CalcularNotasCompetenciasTransversales;
use Illuminate\Http\Request;

class NotasController extends Controller {
    public function obtenerNotasTecnicas($alumnoId, CalcularNotasCompetenciasTecnicas $calcularNotas) {
        $notas = $calcularNotas->calcularNotasTecnicas($alumnoId);

        return response()->json([
            'alumno_id' => $alumnoId,
            'notas_competenciasTec' => array_values($notas),
        ]);
    }

    public function obtenerNotasTransversales($alumnoId, CalcularNotasCompetenciasTransversales $calcularNotas) {
        $notas = $calcularNotas->calcularNotasTransversales($alumnoId);

        return response()->json([
            'estancia_id' => $notas['estancia_id'] ?? null,
            'nota_media' => $notas['nota_media'] ?? null,
        ]);
    }

    public function obtenerNotasEgibide($alumnoId) {
        $notas = NotaAsignatura::where('alumno_id', $alumnoId)->get(["nota", "asignatura_id"]);

        return response()->json($notas);
    }

    public function guardarNotasEgibide(Request $request) {
        $validated = $request->validate([
            'alumno_id' => ['required'],
            'nota' => ['required'],
            'asignatura_id' => ['required'],
        ]);

        $year = date("Y");

        NotaAsignatura::updateOrCreate(
            [
                'alumno_id' => $validated['alumno_id'],
                'asignatura_id' => $validated['asignatura_id'],
                'anio' => $year,
            ],
            [
                'nota' => $validated['nota'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Nota egibide agregada correctamente'
        ], 201);
    }

    public function obtenerNotaCuadernoByAlumno($alumnoId) {

        $notaCuaderno = NotaCuaderno::where('alumno_id', $alumnoId)->first();
 

        return response()->json($notaCuaderno);
    }

    public function guardarNotasCuaderno(Request $request) {
        $validated = $request->validate([
            'alumno_id' => ['required'],
            'nota' => ['required']
        ]);

        NotaCuaderno::updateOrCreate(
            ['alumno_id' => $validated['alumno_id']],
            ['nota' => $validated['nota']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Nota del cuaderno guardada correctamente'
        ], 201);
    }
}

