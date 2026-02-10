<?php

namespace App\Http\Controllers;

use App\Models\Estancia;
use App\Models\HorarioDia;
use App\Models\HorarioTramo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class HorarioController extends Controller
{
    /**
     * Asignar horarios a una estancia
     */
    public function asignarHorario(Request $request)
    {
        try {
            $data = $request->validate([
                'estancia_id' => 'required|exists:estancias,id',
                'horarios' => 'required|array|min:1',
                'horarios.*.dia_semana' => 'required|in:Lunes,Martes,Miercoles,Jueves,Viernes',
                'horarios.*.tramos' => 'required|array|min:1',
                'horarios.*.tramos.*.hora_inicio' => 'required|date_format:H:i',
                'horarios.*.tramos.*.hora_fin' => 'required|date_format:H:i',
            ]);

            $estancia = Estancia::findOrFail($data['estancia_id']);

            // Usar transacción para asegurar integridad
            DB::beginTransaction();

            try {
                // Borrar horarios anteriores si existen
                $estancia->horariosDia()->delete();

                // Insertar horarios nuevos
                foreach ($data['horarios'] as $h) {
                    // Crear día
                    $dia = HorarioDia::create([
                        'dia_semana' => $h['dia_semana'],
                        'estancia_id' => $estancia->id,
                    ]);

                    // Crear tramos dentro del día
                    foreach ($h['tramos'] as $t) {
                        HorarioTramo::create([
                            'hora_inicio' => $t['hora_inicio'],
                            'hora_fin' => $t['hora_fin'],
                            'horario_dia_id' => $dia->id,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Horario asignado correctamente',
                    'horario' => $estancia->load('horariosDia.horariosTramo')
                ], 201);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error en asignarHorario:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'message' => 'Error al asignar horario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horario completo de una estancia
     */
    public function getHorario($idEstancia)
    {
        try {
            $estancia = Estancia::with('horariosDia.horariosTramo')
                ->findOrFail($idEstancia);

            return response()->json($estancia->horariosDia);
        } catch (Exception $e) {
            Log::error('Error en getHorario:', ['message' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al obtener horario'
            ], 500);
        }
    }
}