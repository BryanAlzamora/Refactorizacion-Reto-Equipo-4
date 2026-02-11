<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\AlumnoEntrega;
use App\Models\Alumnos;
use App\Models\EntregaCuaderno;
use Illuminate\Http\Request;

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
        $path = $file->storeAs('public/entregas', $filename);

        // Crear registro en la base de datos
        $entrega = AlumnoEntrega::create([
            'alumno_id' => $alumno->id,
            'url_entrega' => $filename,
            'entrega_id' => (int) $request->input('entrega_id'),
            'fecha_entrega' => now(),
        ]);

        return response()->json($entrega, 201);
    }
}
