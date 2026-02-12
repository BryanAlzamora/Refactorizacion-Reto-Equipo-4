<?php

namespace App\Services;

use App\Models\User;
use App\Models\TutorEgibide;
use App\Models\Ciclos;
use App\Models\Alumnos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ImportacionService
{

    /**
     * Limpia la codificación para evitar errores JSON Malformed
     */
    private function limpiarCodificacion(string $content): string
    {
        return mb_convert_encoding($content, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    }

    public function importarAsignaciones(UploadedFile $file): array
    {
        Log::info('=== INICIO IMPORTACIÓN ASIGNACIONES ===');

        $content = file_get_contents($file->getRealPath());
        $content = $this->limpiarCodificacion($content);

        $delimiter = str_contains($content, ';') ? ';' : ',';
        $lines = explode("\n", str_replace("\r", "", trim($content)));
        $header = str_getcsv(array_shift($lines), $delimiter);

        if (isset($header[0])) {
            $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]/', '', $header[0]);
        }

        $data = [];
        foreach ($lines as $line) {
            if (empty(trim($line)))
                continue;
            $fields = str_getcsv($line, $delimiter);
            if (count($header) == count($fields)) {
                $data[] = array_combine($header, $fields);
            }
        }

        return DB::transaction(function () use ($data) {
            return $this->procesarAsignaciones($data);
        });
    }

    private function procesarAsignaciones(array $data): array
    {
        $stats = [
            'usuarios_creados' => 0,
            'usuarios_actualizados' => 0,
            'tutores_creados' => 0,
            'tutores_actualizados' => 0,
            'ciclos_creados' => 0,
            'ciclos_actualizados' => 0,
            'asignaturas_creadas' => 0,
            'asignaturas_actualizadas' => 0,
            'relaciones_tutor_asignatura_creadas' => 0,
            'relaciones_tutor_ciclo_creadas' => 0,
            'filas_procesadas' => 0,
            'errores' => []
        ];

        $tutoresCache = [];
        $ciclosCache = [];
        $asignaturasCache = [];
        $relacionesTutorCicloCreadas = [];

        foreach ($data as $index => $row) {
            try {
                if (empty($row['Alias_Profesor']) || empty($row['Des_Asig']) || empty($row['Grupo'])) {
                    continue;
                }

                $aliasProfesor = trim($row['Alias_Profesor']);
                $nombreAsignatura = trim($row['Des_Asig']);
                $codigoGrupo = trim($row['Grupo']);
                $descripcionGrupo = trim($row['Des_Grupo'] ?? '');

                $tutorId = $this->procesarTutor(
                    $aliasProfesor,
                    trim($row['Nombre'] ?? ''),
                    trim($row['Apel1'] ?? ''),
                    trim($row['Apel2'] ?? ''),
                    $tutoresCache,
                    $stats
                );

                $cicloId = $this->procesarCiclo(
                    $codigoGrupo,
                    $descripcionGrupo,
                    $ciclosCache,
                    $stats
                );

                // Aquí es donde ocurría el error silencioso
                $asignaturaId = $this->procesarAsignatura(
                    $nombreAsignatura,
                    $cicloId,
                    $asignaturasCache,
                    $stats
                );

                // Si procesarAsignatura falla, esta línea nunca se ejecuta
                $this->crearRelacionTutorAsignatura($tutorId, $asignaturaId, $stats);

                $claveTutorCiclo = "{$tutorId}-{$cicloId}";
                if (!isset($relacionesTutorCicloCreadas[$claveTutorCiclo])) {
                    $this->crearRelacionTutorCiclo($tutorId, $cicloId, $stats);
                    $relacionesTutorCicloCreadas[$claveTutorCiclo] = true;
                }

                $stats['filas_procesadas']++;

            } catch (Exception $e) {
                $msg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8, ISO-8859-1');
                Log::error("Error Fila " . ($index + 2) . ": " . $msg);
                // ESTO ES CLAVE: Mira este array en tu respuesta JSON para ver el error SQL exacto
                $stats['errores'][] = "Fila " . ($index + 2) . ": " . $msg;
            }
        }

        return $stats;
    }

    private function procesarTutor($alias, $nombre, $apellido1, $apellido2, &$cache, &$stats): int
    {
        if (isset($cache[$alias]))
            return $cache[$alias];

        $email = strtolower($alias) . "@egibide.org";
        $apellidos = trim($apellido1 . ' ' . $apellido2);

        $user = User::updateOrCreate(
            ['email' => $email],
            ['password' => Hash::make('egibide2025'), 'role' => 'tutor_egibide']
        );
        $user->wasRecentlyCreated ? $stats['usuarios_creados']++ : $stats['usuarios_actualizados']++;

        $tutor = TutorEgibide::updateOrCreate(
            ['user_id' => $user->id],
            ['nombre' => $nombre, 'apellidos' => $apellidos, 'alias' => $alias]
        );
        $tutor->wasRecentlyCreated ? $stats['tutores_creados']++ : $stats['tutores_actualizados']++;

        $cache[$alias] = $tutor->id;
        return $tutor->id;
    }

    private function procesarCiclo($codigoGrupo, $descripcion, &$cache, &$stats): int
    {
        if (isset($cache[$codigoGrupo]))
            return $cache[$codigoGrupo];

        $ciclo = Ciclos::where('grupo', $codigoGrupo)->first();

        if ($ciclo) {
            if ($ciclo->nombre !== $descripcion) {
                $ciclo->update(['nombre' => $descripcion]);
            }
            $stats['ciclos_actualizados']++;
            $cicloId = $ciclo->id;
        } else {
            $ciclo = Ciclos::create(['nombre' => $descripcion, 'grupo' => $codigoGrupo]);
            $stats['ciclos_creados']++;
            $cicloId = $ciclo->id;
        }

        $cache[$codigoGrupo] = $cicloId;
        return $cicloId;
    }

    private function procesarAsignatura($nombre, $cicloId, &$cache, &$stats): int
    {
        $cacheKey = $cicloId . '_' . $nombre;
        if (isset($cache[$cacheKey]))
            return $cache[$cacheKey];

        $asignatura = DB::table('asignaturas')
            ->where('nombre_asignatura', $nombre)
            ->where('ciclo_id', $cicloId)
            ->first();

        if ($asignatura) {
            $stats['asignaturas_actualizadas']++;
            $asignaturaId = $asignatura->id;
        } else {
            $codigoAsignatura = collect(explode(' ', trim($nombre)))
                ->map(function ($palabra) {
                    $palabra = iconv('UTF-8', 'ASCII//TRANSLIT', $palabra);
                    $soloLetras = preg_replace('/[^a-zA-Z]/', '', $palabra);
                    return !empty($soloLetras) ? strtoupper(substr($soloLetras, 0, 1)) : null;
                })
                ->filter() // Elimina los nulos (palabras que eran solo números o símbolos)
                ->implode('');

            try {
                // CORRECCIÓN: He quitado 'curso' y 'horas_semanales' que daban error si no existen en tu BD
                // Solo insertamos los campos OBLIGATORIOS que sabemos que tienes
                $asignaturaId = DB::table('asignaturas')->insertGetId([
                    'codigo_asignatura' => $codigoAsignatura,
                    'nombre_asignatura' => $nombre,
                    'ciclo_id' => $cicloId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $stats['asignaturas_creadas']++;
            } catch (\Exception $e) {
                // Si esto falla, lanzamos la excepción para ver el mensaje SQL exacto en $stats['errores']
                throw new Exception("Error SQL al crear asignatura '$nombre': " . $e->getMessage());
            }
        }

        $cache[$cacheKey] = $asignaturaId;
        return $asignaturaId;
    }

    private function crearRelacionTutorAsignatura(int $tutorId, int $asignaturaId, array &$stats): void
    {
        $existe = DB::table('tutor_asignatura')->where('tutor_id', $tutorId)->where('asignatura_id', $asignaturaId)->exists();
        if (!$existe) {
            DB::table('tutor_asignatura')->insert([
                'tutor_id' => $tutorId,
                'asignatura_id' => $asignaturaId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $stats['relaciones_tutor_asignatura_creadas']++;
        }
    }

    private function crearRelacionTutorCiclo(int $tutorId, int $cicloId, array &$stats): void
    {
        $existe = DB::table('ciclo_tutor')->where('tutor_id', $tutorId)->where('ciclo_id', $cicloId)->exists();
        if (!$existe) {
            DB::table('ciclo_tutor')->insert([
                'tutor_id' => $tutorId,
                'ciclo_id' => $cicloId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $stats['relaciones_tutor_ciclo_creadas']++;
        }
    }

    public function importar(UploadedFile $file): array
    {
        // ... (Tu código de importar alumnos/excel se mantiene igual, omitido por brevedad pero inclúyelo) ...
        // Para que funcione el copy-paste, te pongo la estructura básica aquí:

        Log::info('=== INICIO IMPORTACIÓN ===');
        $content = file_get_contents($file->getRealPath());
        $content = $this->limpiarCodificacion($content);
        $delimiter = str_contains($content, ';') ? ';' : ',';
        $lines = explode("\n", str_replace("\r", "", trim($content)));
        $header = str_getcsv(array_shift($lines), $delimiter);
        if (isset($header[0]))
            $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]/', '', $header[0]);
        $data = [];
        foreach ($lines as $line) {
            if (empty(trim($line)))
                continue;
            $fields = str_getcsv($line, $delimiter);
            if (count($header) == count($fields))
                $data[] = array_combine($header, $fields);
        }

        return DB::transaction(function () use ($data, $header) {
            if (in_array('DNI ALUMNO', $header))
                return $this->procesarAlumnos($data);
            if (in_array('Alias_Profesor', $header))
                return $this->procesarAsignaciones($data);
            throw new Exception("Formato no reconocido.");
        });
    }

    private function procesarAlumnos(array $data): array
    {
        $stats = ['usuarios_creados' => 0, 'usuarios_actualizados' => 0, 'alumnos_creados' => 0, 'alumnos_actualizados' => 0, 'filas_procesadas' => 0, 'errores' => []];

        foreach ($data as $index => $row) {
            try {
                if (empty($row['DNI ALUMNO']) || empty($row['EMAIL ALUMNO']))
                    continue;

                $dni = trim($row['DNI ALUMNO']);
                $email = trim($row['EMAIL ALUMNO']);
                $nombre = trim($row['NOMBRE ALUMNO'] ?? '');
                $apellido1 = trim($row['APELLIDO1 ALUMNO'] ?? '');
                $apellido2 = trim($row['APELLIDO2 ALUMNO'] ?? '');
                $grupo = trim($row['CLASE'] ?? $row['GRUPO'] ?? '');
                $matricula = $row['MATRICULA ALUMNO'] ?? null;

                // Crear Ciclo si no existe (SIN FAMILIA)
                if ($grupo) {
                    $ciclo = Ciclos::where('grupo', $grupo)->first();
                    if (!$ciclo) {
                        try {
                            Ciclos::create(['nombre' => "Ciclo $grupo", 'grupo' => $grupo]);
                        } catch (\Exception $e) { /* Ignorar duplicados */
                        }
                    }
                }

                $user = User::updateOrCreate(['email' => $email], ['password' => Hash::make($dni), 'role' => 'alumno']);
                if ($user->wasRecentlyCreated)
                    $stats['usuarios_creados']++;
                else
                    $stats['usuarios_actualizados']++;

                $alumno = Alumnos::updateOrCreate(
                    ['dni' => $dni],
                    ['user_id' => $user->id, 'nombre' => $nombre, 'apellidos' => "$apellido1 $apellido2", 'matricula_id' => $matricula, 'grupo' => $grupo ?: null]
                );
                if ($alumno->wasRecentlyCreated)
                    $stats['alumnos_creados']++;
                else
                    $stats['alumnos_actualizados']++;

                $stats['filas_procesadas']++;

            } catch (Exception $e) {
                $msg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8, ISO-8859-1');
                $stats['errores'][] = "Fila " . ($index + 2) . ": " . $msg;
            }
        }
        return $stats;
    }
}
