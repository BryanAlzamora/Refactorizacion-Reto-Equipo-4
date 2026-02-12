<?php

namespace App\Http\Controllers;

use App\Models\Alumnos;
use App\Models\Ciclos;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TutorEgibide;
use App\Models\Estancia;
use App\Models\Empresas;
use App\Models\EntregaCuaderno;

class TutorEgibideController extends Controller
{

    public function getAlumnosByCurrentTutor(Request $request)
    {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $alumnos = $tutor->alumnos()->with('estancias.empresa')->get();

        return response()->json($alumnos);
    }

    public function conseguirEmpresasporTutor(Request $request)
    {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $empresas = Empresas::whereHas('estancias.alumno', function ($query) use ($tutor) {
            $query->where('tutor_id', $tutor->id);
        })->get();

        return response()->json($empresas);
    }


    public function getDetalleEmpresa(Request $request, $empresaId)
    {
        $userId = $request->user()->id;
        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $empresa = Empresas::with([
            'instructores' => function ($query) use ($tutor) {
                $query->whereHas('estancias.alumno', function ($q) use ($tutor) {
                    $q->where('tutor_id', $tutor->id);
                });
            }
        ])
            ->where('id', $empresaId)
            ->whereHas('estancias.alumno', function ($query) use ($tutor) {
                $query->where('tutor_id', $tutor->id);
            })
            ->firstOrFail();

        return response()->json($empresa);
    }


    public function getTutoresByCiclo($ciclo_id)
    {
        $ciclo = Ciclos::find($ciclo_id);
        if (!$ciclo)
            return response()->json([], 404);

        $tutores = $ciclo?->tutores ?? collect();

        return response()->json($tutores, 200);
    }

    public function inicioTutor(Request $request)
    {
        $user = $request->user();

        $tutor = $user->tutorEgibide;

        if (!$tutor) {
            return response()->json([
                'message' => 'El usuario no tiene tutor egibide asociado.'
            ], 404);
        }

        $email = $user->email;
        $tutor['email'] = $email;
        $hoy = now();

        $alumnosAsignados = $tutor->alumnos()->count();
        $alumnosConEstancia = $tutor->alumnos()
            ->whereHas('estancias', function ($q) use ($hoy) {
                $q->whereDate('fecha_inicio', '<=', $hoy)
                    ->where(function ($q2) use ($hoy) {
                        $q2->whereNull('fecha_fin')
                            ->orWhereDate('fecha_fin', '>=', $hoy);
                    })
                    ->whereNotNull('empresa_id');
            })
            ->count();


        return response()->json([
            'tutor' => $tutor,
            'counts' => [
                'alumnos_asignados' => $alumnosAsignados,
                'empresas_asignadas' => $alumnosConEstancia,
            ],
        ]);
    }

    public function asignarAlumno(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'tutor_id' => 'required|exists:users,id', // suponiendo que tutores son usuarios
        ]);

        $alumno = Alumnos::find($request->alumno_id);
        $tutorId = $request->tutor_id;

        try {
            // Asignación simple, suponiendo campo tutor_id en tabla alumnos
            $alumno->tutor_id = $tutorId;
            $alumno->save();

            return response()->json([
                'success' => true,
                'message' => 'Alumno asignado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar alumno',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $tutor = TutorEgibide::where('user_id', $user->id)->first();

        return response()->json([
            'id' => $tutor->id,
            'nombre' => $tutor->nombre,
            'apellidos' => $tutor->apellidos,
            'email' => $user->email,
            'tipo' => $user->tipo,
        ]);
    }

    /**
     * Guardar o actualizar horario y calendario de una estancia.
     */
    public function horasperiodo(Request $request)
    {
        // Validar los datos
        $validated = $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'horas_totales' => 'required|integer|min:1',
        ]);

        try {
            // Obtener tutor logueado
            $user = $request->user();

            // Crear o actualizar estancia por alumno_id
            $estancia = Estancia::updateOrCreate(
                ['alumno_id' => $validated['alumno_id']], // Condición para actualizar
                [
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin'] ?? null,
                    'horas_totales' => $validated['horas_totales'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Horario y calendario guardados correctamente',
                'estancia' => $estancia,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la estancia: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMisCursosConAlumnosSinTutor(Request $req, $tutorId)
    {
        $tutor = TutorEgibide::find($tutorId);

        // Obtenemos los cursos del tutor con su ciclo y alumnos sin tutor asignado
        $cursos = $tutor->ciclos()
            ->with([
                'alumnos' => function ($query) {
                    $query->whereNull('tutor_id'); // solo alumnos sin tutor
                }
            ])
            ->get();

        return response()->json($cursos);
    }

    public function getEntregasByCurrentTutor(Request $req, $tutorId)
    {
        $entregas = EntregaCuaderno::with([
            'entregas.alumno'
        ])
            ->where('tutor_id', $tutorId)
            ->get();

        return response()->json($entregas);
    }

    public function conseguirTodasLasEmpresas(Request $request)
    {
        $empresas = Empresas::all();
        return response()->json($empresas);
    }

    // Método para asignar/actualizar instructor en una empresa
    public function asignarInstructor(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'ciudad' => 'nullable|string|max:255',
        ]);

        try {
            // Verificar si ya existe un instructor para esta empresa
            $instructor = TutorEmpresa::where('empresa_id', $validated['empresa_id'])->first();

            if ($instructor) {
                // Actualizar instructor existente
                $instructor->update([
                    'nombre' => $validated['nombre'],
                    'apellidos' => $validated['apellidos'],
                    'telefono' => $validated['telefono'] ?? null,
                    'ciudad' => $validated['ciudad'] ?? null,
                ]);
                $mensaje = 'Instructor actualizado correctamente';
            } else {
                // Crear nuevo instructor
                $instructor = TutorEmpresa::create([
                    'nombre' => $validated['nombre'],
                    'apellidos' => $validated['apellidos'],
                    'telefono' => $validated['telefono'] ?? null,
                    'ciudad' => $validated['ciudad'] ?? null,
                    'empresa_id' => $validated['empresa_id'],
                    'user_id' => null, // Se puede asignar después si es necesario
                ]);
                $mensaje = 'Instructor creado correctamente';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'instructor' => $instructor
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar instructor: ' . $e->getMessage(),
            ], 500);
        }
    }
}
