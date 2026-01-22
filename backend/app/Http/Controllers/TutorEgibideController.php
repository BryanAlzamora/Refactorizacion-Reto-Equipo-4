<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TutorEgibide;
use App\Models\Estancia;
use App\Models\Empresas; 


class TutorEgibideController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    public function getAlumnosByCurrentTutor(Request $request) {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        $alumnos = $tutor->alumnosConEstancia()->get();

        return response()->json($alumnos);
    }

    public function conseguirEmpresasporTutor(Request $request) {
        $userId = $request->user()->id;

        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();

        // Obtener empresas únicas a través de las estancias
        $empresas = Empresas::whereHas('estancias', function($query) use ($tutor) {
            $query->where('tutor_id', $tutor->id);
        })->get();

        return response()->json($empresas);
    }

    public function getDetalleEmpresa(Request $request, $empresaId) {
        $userId = $request->user()->id;
        $tutor = TutorEgibide::where('user_id', $userId)->firstOrFail();
        
        // Obtener empresa con instructor
        $empresa = Empresas::with(['instructores' => function($query) use ($tutor) {
            $query->whereHas('estancias', function($q) use ($tutor) {
                $q->where('tutor_id', $tutor->id);
            });
        }])
        ->where('id', $empresaId)
        ->whereHas('estancias', function($query) use ($tutor) {
            $query->where('tutor_id', $tutor->id);
        })
        ->firstOrFail();
        
        return response()->json($empresa);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TutorEgibide $tutorEgibide) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TutorEgibide $tutorEgibide) {
        //
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
}
