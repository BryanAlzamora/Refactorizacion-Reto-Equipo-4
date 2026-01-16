<?php

namespace App\Http\Controllers;

use App\Models\Empresas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpresasController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        return Empresas::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'nombre' => ['required'],
            'cif' => ['required'],
            'telefono' => ['required'],
            'email' => ['required'],
            'direccion' => ['required']
        ]);

        Empresas::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Empresa agregada correctamente'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Empresas $empresas) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresas $empresas) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresas $empresas) {
        //
    }

    public function miEmpresa()
{
    $userId = auth()->id();

    $empresa = DB::table('alumnos')
        ->join('estancias', 'alumnos.id_alumno', '=', 'estancias.id_alumno')
        ->join('empresas', 'estancias.id_empresa', '=', 'empresas.id_empresa')
        ->where('alumnos.user_id', $userId)
        ->select(
            'empresas.cif',
            'empresas.nombre',
            'empresas.telefono',
            'empresas.email',
            'empresas.direccion',
        )
        ->first();

    if (!$empresa) {
        return response()->json(['message' => 'Empresa no encontrada para este alumno'], 404);
    }

    return response()->json($empresa);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresas $empresas) {
        //
    }
}
