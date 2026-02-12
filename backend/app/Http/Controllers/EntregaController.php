<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entrega;
use App\Models\EntregaCuaderno;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class EntregaController extends Controller
{
     public function mias(Request $request){
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($user->role !== 'alumno') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $alumno = $user->alumno;


        if (!$alumno) {
            return response()->json(['message' => 'No existe alumno asociado a este usuario'], 404);
        }

        $entregas = EntregaCuaderno::where('tutor_id', $alumno->tutor_id)
            ->with(['entregas' => function ($query) use ($alumno) {
                $query->where('alumno_id', $alumno->id);
            }])
            ->get();

        return response()->json($entregas);
    }

    public function archivo(Request $request, EntregaCuaderno $entrega){
        $path = ltrim((string) $entrega->archivo, '/');

        if (!Storage::disk('public')->exists($path)) {

            $alt = 'entregas/' . $path;

            if (Storage::disk('public')->exists($alt)) {
                $path = $alt;
            } else {
                return response()->json(['message' => 'Archivo no encontrado'], 404);
            }
        }

        $absolutePath = Storage::disk('public')->path($path);

        return response()->download($absolutePath, basename($path));
    }

    public function destroy($id){
        $entrega = DB::table('entregas')->where('id', $id)->first();

        if (!$entrega) {
            return response()->json(['message' => 'Entrega no encontrada'], 404);
        }
        if ($entrega->archivo && Storage::disk('public')->exists($entrega->archivo)) {
            Storage::disk('public')->delete($entrega->archivo);
        }
        DB::table('entregas')->where('id', $id)->delete();

        return response()->json(['message' => 'Entrega eliminada correctamente']);
    }

    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255',
            'fecha_limite' => 'required|date',
            'tutor_id' => 'required|integer|exists:users,id', // Asumiendo que tus tutores están en la tabla users
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Datos inválidos',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $entrega = EntregaCuaderno::create([
                'descripcion' => $request->descripcion,
                'fecha_creacion' => now(),
                'fecha_limite' => $request->fecha_limite,
                'tutor_id' => $request->tutor_id,
            ]);

            return response()->json([
                'message' => 'Entrega creada correctamente',
                'entrega' => $entrega
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear la entrega',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
