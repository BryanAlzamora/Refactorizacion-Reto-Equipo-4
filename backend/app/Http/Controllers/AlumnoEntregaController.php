<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\AlumnoEntrega;
use App\Models\Alumnos;
use App\Models\EntregaCuaderno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AlumnoEntregaController extends Controller
{
    public function storeEntrega(Request $request, $alumnoId)
    {
        // Validar que el alumno exista
        $alumno = Alumnos::find($alumnoId);
        if (!$alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        // Validar archivo
        $request->validate([
            'archivo' => 'required|file|max:10240|mimes:pdf,doc,docx,zip',
            'entrega_id' => 'required|exists:entrega_cuaderno,id'
        ]);

        $file = $request->file('archivo');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Guardar el archivo en storage/app/public/entregas
        $path = $file->storeAs('entregas', $filename, 'public');

        $entregaExistente = AlumnoEntrega::where('entrega_id', $request->input('entrega_id'))
            ->where('alumno_id', $alumno->id)
            ->first();

        if ($entregaExistente && $entregaExistente->url_entrega) {
            Storage::disk('public')->delete('entregas/' . $entregaExistente->url_entrega);
        }

        // Crear o actualizar registro en la base de datos
        $entrega = AlumnoEntrega::updateOrCreate([
            'entrega_id' => (int) $request->input('entrega_id'),
            'alumno_id' => $alumno->id
        ], [
            'alumno_id' => $alumno->id,
            'url_entrega' => $filename,
            'entrega_id' => (int) $request->input('entrega_id'),
            'fecha_entrega' => now(),
        ]);

        return response()->json([$entrega], 201);
    }

    public function actualizar(Request $request, $idEntrega)
    {
        $entrega = AlumnoEntrega::find($idEntrega);

        $data = $request->validate([
            'observaciones' => 'required|string',
            'feedback' => 'required|string',
        ]);

        $entrega = $entrega->update($data);

        return response()->json($entrega);
    }
}
