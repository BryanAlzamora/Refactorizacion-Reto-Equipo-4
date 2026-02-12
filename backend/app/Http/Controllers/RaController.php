<?php

namespace App\Http\Controllers;

use App\Models\ResultadoAprendizaje;
use Illuminate\Http\Request;

class RaController extends Controller
{
    public function store(Request $request)
{
    $ra = ResultadoAprendizaje::create([
        'descripcion' => $request->descripcion,
        'asignatura_id' => $request->asignatura_id,
    ]);

    return response()->json($ra, 201);
}

public function destroy($id)
{
    $ra = ResultadoAprendizaje::findOrFail($id);
    $ra->delete();

    return response()->json(['message' => 'RA eliminada']);
}

}
