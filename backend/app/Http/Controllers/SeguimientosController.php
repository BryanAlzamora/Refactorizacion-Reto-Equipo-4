<?php

namespace App\Http\Controllers;

use App\Models\Seguimiento;
use Database\Seeders\SeguimientosSeeder;
use Illuminate\Http\Request;

class SeguimientosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Seguimiento::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Seguimiento $seguimiento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seguimiento $seguimiento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Seguimiento $seguimiento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $seguimiento = Seguimiento::find($id);

        if (!$seguimiento) {
            return response()->json(['message' => 'Seguimiento no encontrado'], 404);
        }

        $seguimiento->delete();

        return response()->json(['message' => 'Seguimiento eliminado correctamente']);
    }

    public function seguimientosAlumno($alumno_Id)
    {
        $seguimientos = Seguimiento::with('estancia')
            ->whereHas('estancia', function ($q) use ($alumno_Id) {
                $q->where('alumno_id', $alumno_Id);
            })
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json($seguimientos);
    }

    public function nuevoSeguimiento(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|integer|exists:alumnos,id',
            'fecha' => 'required|date',
            'accion' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'via' => 'nullable|string|max:50'
        ]);

        // Buscar la estancia del alumno
        $estancia = \App\Models\Estancia::where('alumno_id', $request->alumno_id)->first();

        if (!$estancia) {
            return response()->json(['message' => 'Estancia del alumno no encontrada'], 404);
        }

        // Crear seguimiento
        $seguimiento = \App\Models\Seguimiento::create([
            'accion' => $request->accion,
            'fecha' => $request->fecha,
            'descripcion' => $request->descripcion,
            'via' => $request->via,
            'estancia_id' => $estancia->id,
        ]);

        return response()->json(['message' => 'Seguimiento creado', 'seguimiento' => $seguimiento]);
    }

}
