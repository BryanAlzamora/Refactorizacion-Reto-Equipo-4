<?php

namespace App\Http\Controllers;

use App\Models\TutorEmpresa;
use App\Models\Empresas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class InstructorController extends Controller
{
    /**
     * Obtener todos los instructores (opcionalmente filtrados por empresa)
     */
    public function index(Request $request)
    {
        try {
            $empresaId = $request->query('empresa_id');

            $query = TutorEmpresa::with('empresa');

            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            $instructores = $query->get();
            
            Log::info('Instructores obtenidos', [
                'empresa_id' => $empresaId,
                'total' => $instructores->count()
            ]);

            return response()->json($instructores);
            
        } catch (Exception $e) {
            Log::error('Error al obtener instructores', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener instructores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo instructor
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellidos' => 'required|string|max:150',
                'telefono' => 'nullable|string|max:20',
                'ciudad' => 'nullable|string|max:120',
                'empresa_id' => 'required|exists:empresas,id',
                'email' => 'nullable|email|unique:users,email',
            ]);

            DB::beginTransaction();

            try {
                // Si se proporciona email, crear usuario
                $userId = null;
                if (!empty($validated['email'])) {
                    $user = User::create([
                        'email' => $validated['email'],
                        'password' => Hash::make('12345Abcde'), // Contraseña temporal
                        'role' => 'tutor_empresa',
                    ]);
                    $userId = $user->id;
                    
                    Log::info('Usuario creado para instructor', [
                        'user_id' => $userId,
                        'email' => $validated['email']
                    ]);
                }

                // Crear instructor
                $instructor = TutorEmpresa::create([
                    'nombre' => $validated['nombre'],
                    'apellidos' => $validated['apellidos'],
                    'telefono' => $validated['telefono'] ?? null,
                    'ciudad' => $validated['ciudad'] ?? null,
                    'empresa_id' => $validated['empresa_id'],
                    'user_id' => $userId,
                ]);

                DB::commit();
                
                Log::info('Instructor creado exitosamente', [
                    'instructor_id' => $instructor->id,
                    'empresa_id' => $validated['empresa_id']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Instructor creado correctamente',
                    'instructor' => $instructor->load('empresa')
                ], 201);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            Log::warning('Validación fallida al crear instructor', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Error al crear instructor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un instructor específico
     */
    public function show($id)
    {
        try {
            Log::info('Buscando instructor', ['id' => $id]);
            
            // Cargar instructor con empresa
            $instructor = TutorEmpresa::with('empresa')->find($id);
            
            if (!$instructor) {
                Log::warning('Instructor no encontrado', ['id' => $id]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Instructor no encontrado'
                ], 404);
            }
            
            Log::info('Instructor encontrado', [
                'instructor_id' => $instructor->id,
                'nombre' => $instructor->nombre
            ]);

            return response()->json([
                'success' => true,
                'instructor' => $instructor
            ]);
            
        } catch (Exception $e) {
            Log::error('Error al obtener instructor', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener instructor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un instructor
     */
    public function update(Request $request, $id)
    {
        try {
            $instructor = TutorEmpresa::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellidos' => 'required|string|max:150',
                'telefono' => 'nullable|string|max:20',
                'ciudad' => 'nullable|string|max:120',
                'empresa_id' => 'required|exists:empresas,id',
            ]);

            $instructor->update($validated);
            
            Log::info('Instructor actualizado', [
                'instructor_id' => $instructor->id,
                'cambios' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instructor actualizado correctamente',
                'instructor' => $instructor->load('empresa')
            ]);

        } catch (ValidationException $e) {

            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (Exception $e) {
            Log::error('Error al actualizar instructor', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un instructor
     */
    public function destroy($id)
    {
        try {
            $instructor = TutorEmpresa::findOrFail($id);
            
            // Mantener las estancias pero sin instructor
            $instructor->estancias()->update(['instructor_id' => null]);
            
            
            $instructor->delete();
            

            return response()->json([
                'success' => true,
                'message' => 'Instructor eliminado correctamente'
            ]);
            
        } catch (Exception $e) {
            Log::error('Error al eliminar instructor', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar instructor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar instructor a una estancia de alumno
     */
    public function asignarAEstancia(Request $request)
    {
        try {
            $validated = $request->validate([
                'estancia_id' => 'required|exists:estancias,id',
                'instructor_id' => 'required|exists:instructores,id',
            ]);

            $estancia = \App\Models\Estancia::findOrFail($validated['estancia_id']);
            $estancia->instructor_id = $validated['instructor_id'];
            $estancia->save();
            
            Log::info('Instructor asignado a estancia', [
                'estancia_id' => $validated['estancia_id'],
                'instructor_id' => $validated['instructor_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Instructor asignado correctamente a la estancia'
            ]);

        } catch (Exception $e) {
            Log::error('Error al asignar instructor a estancia', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar instructor: ' . $e->getMessage()
            ], 500);
        }
    }
}