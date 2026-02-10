<?php

namespace App\Services;

use App\Models\User;
use App\Models\TutorEgibide;
use App\Models\Ciclos;
use App\Models\Alumnos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ImportacionService {

    /**
     * Importa asignaciones de tutores y asignaturas desde archivo CSV
     *
     * Estructura esperada del CSV de asignaciones:
     * Campus;Grupo;Modelo;Regimen;Des_Grupo;Des_Asig;Alias_Profesor;Nombre;Apel1;Apel2
     */
    public function importarAsignaciones(UploadedFile $file): array
    {
        $content = file_get_contents($file->getRealPath());
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        if ($encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        $delimiter = str_contains($content, ';') ? ';' : ',';
        $lines = explode("\n", str_replace("\r", "", trim($content)));
        $header = str_getcsv(array_shift($lines), $delimiter);
        // Eliminar BOM si existe
        $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]/', '', $header[0]);

        $data = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $fields = str_getcsv($line, $delimiter);
            if (count($header) == count($fields)) {
                $data[] = array_combine($header, $fields);
            }
        }

        return DB::transaction(function () use ($data) {
            return $this->procesarAsignaciones($data);
        });
    }

    /**
     * Procesa los datos de asignaciones
     */
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

        // Cache para evitar consultas repetidas
        $tutoresCache = [];
        $ciclosCache = [];
        $asignaturasCache = [];
        $relacionesTutorCicloCreadas = []; // Para evitar duplicados en la relación

        foreach ($data as $index => $row) {
            try {
                // Validar datos obligatorios
                if (empty($row['Alias_Profesor']) || empty($row['Des_Asig']) || empty($row['Grupo'])) {
                    $stats['errores'][] = "Fila " . ($index + 2) . ": Campos obligatorios vacíos";
                    continue;
                }

                $aliasProfesor = trim($row['Alias_Profesor']);
                $nombreAsignatura = trim($row['Des_Asig']);
                $codigoGrupo = trim($row['Grupo']); // Este es el identificador del ciclo
                $descripcionGrupo = trim($row['Des_Grupo'] ?? '');

                // 1. PROCESAR TUTOR (Usuario + Perfil)
                $tutorId = $this->procesarTutor(
                    $aliasProfesor,
                    trim($row['Nombre'] ?? ''),
                    trim($row['Apel1'] ?? ''),
                    trim($row['Apel2'] ?? ''),
                    $tutoresCache,
                    $stats
                );

                // 2. PROCESAR CICLO usando el campo 'grupo' como identificador
                $cicloId = $this->procesarCiclo(
                    $codigoGrupo,
                    $descripcionGrupo,
                    $ciclosCache,
                    $stats
                );

                // 3. PROCESAR ASIGNATURA
                $asignaturaId = $this->procesarAsignatura(
                    $nombreAsignatura,
                    $cicloId,
                    $asignaturasCache,
                    $stats
                );

                // 4. CREAR RELACIÓN TUTOR-ASIGNATURA (tabla pivot tutor_asignatura)
                $this->crearRelacionTutorAsignatura(
                    $tutorId,
                    $asignaturaId,
                    $stats
                );

                // 5. CREAR RELACIÓN TUTOR-CICLO (tabla pivot ciclo_tutor) si no existe
                $claveTutorCiclo = "{$tutorId}-{$cicloId}";
                if (!isset($relacionesTutorCicloCreadas[$claveTutorCiclo])) {
                    $this->crearRelacionTutorCiclo($tutorId, $cicloId, $stats);
                    $relacionesTutorCicloCreadas[$claveTutorCiclo] = true;
                }

                $stats['filas_procesadas']++;

            } catch (Exception $e) {
                $stats['errores'][] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * Procesa un tutor (crea o actualiza usuario y perfil)
     */
    private function procesarTutor(
        string $alias,
        string $nombre,
        string $apellido1,
        string $apellido2,
        array &$cache,
        array &$stats
    ): int {
        if (isset($cache[$alias])) {
            return $cache[$alias];
        }

        $email = strtolower($alias) . "@egibide.org";
        $apellidos = trim($apellido1 . ' ' . $apellido2);

        // 1. Crear o actualizar Usuario
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'password' => Hash::make('egibide2025'),
                'role' => 'tutor_egibide'
            ]
        );

        if ($user->wasRecentlyCreated) {
            $stats['usuarios_creados']++;
        } else {
            $stats['usuarios_actualizados']++;
        }

        // 2. Crear o actualizar perfil Tutor usando el modelo
        $tutor = TutorEgibide::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'alias' => $alias,
            ]
        );

        if ($tutor->wasRecentlyCreated) {
            $stats['tutores_creados']++;
        } else {
            $stats['tutores_actualizados']++;
        }

        $cache[$alias] = $tutor->id;
        return $tutor->id;
    }

    /**
     * Procesa un ciclo usando el campo 'grupo' como identificador único
     *
     * IMPORTANTE: En tu esquema, 'grupo' es el identificador único del ciclo
     * El campo 'codigo' NO EXISTE en la tabla ciclos
     */
    private function procesarCiclo(
        string $codigoGrupo,
        string $descripcion,
        array &$cache,
        array &$stats
    ): int {
        if (isset($cache[$codigoGrupo])) {
            return $cache[$codigoGrupo];
        }

        // Buscar por el campo 'grupo' que es único
        $ciclo = Ciclos::where('grupo', $codigoGrupo)->first();

        if ($ciclo) {
            // Actualizar nombre si ha cambiado
            if ($ciclo->nombre !== $descripcion) {
                $ciclo->update(['nombre' => $descripcion]);
            }
            $stats['ciclos_actualizados']++;
            $cicloId = $ciclo->id;
        } else {
            // Obtener familia profesional por defecto
            $familiaPorDefecto = DB::table('familias_profesionales')->first();

            if (!$familiaPorDefecto) {
                throw new Exception("No hay familias profesionales en la base de datos. Crea al menos una antes de importar.");
            }

            // Crear nuevo ciclo
            $ciclo = Ciclos::create([
                'nombre' => $descripcion,
                'familia_profesional_id' => $familiaPorDefecto->id,
                'grupo' => $codigoGrupo
            ]);

            $stats['ciclos_creados']++;
            $cicloId = $ciclo->id;
        }

        $cache[$codigoGrupo] = $cicloId;
        return $cicloId;
    }

    /**
     * Procesa una asignatura
     */
    private function procesarAsignatura(
        string $nombre,
        int $cicloId,
        array &$cache,
        array &$stats
    ): int {
        // Cache key: nombre + ciclo
        $cacheKey = $cicloId . '_' . $nombre;

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // Buscar asignatura existente por nombre y ciclo
        $asignatura = DB::table('asignaturas')
            ->where('nombre_asignatura', $nombre)
            ->where('ciclo_id', $cicloId)
            ->first();

        if ($asignatura) {
            $stats['asignaturas_actualizadas']++;
            $asignaturaId = $asignatura->id;
        } else {
            // Generar código único para la asignatura
            $codigoAsignatura = 'ASIG-' . $cicloId . '-' . substr(md5($nombre), 0, 6);

            // Crear nueva asignatura
            $asignaturaId = DB::table('asignaturas')->insertGetId([
                'codigo_asignatura' => $codigoAsignatura,
                'nombre_asignatura' => $nombre,
                'ciclo_id' => $cicloId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $stats['asignaturas_creadas']++;
        }

        $cache[$cacheKey] = $asignaturaId;
        return $asignaturaId;
    }

    /**
     * Crea la relación entre tutor y asignatura en la tabla pivot
     */
    private function crearRelacionTutorAsignatura(
        int $tutorId,
        int $asignaturaId,
        array &$stats
    ): void {
        // Verificar si ya existe la relación
        $existe = DB::table('tutor_asignatura')
            ->where('tutor_id', $tutorId)
            ->where('asignatura_id', $asignaturaId)
            ->exists();

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

    /**
     * Crea la relación entre tutor y ciclo en la tabla pivot
     */
    private function crearRelacionTutorCiclo(
        int $tutorId,
        int $cicloId,
        array &$stats
    ): void {
        // Verificar si ya existe la relación
        $existe = DB::table('ciclo_tutor')
            ->where('tutor_id', $tutorId)
            ->where('ciclo_id', $cicloId)
            ->exists();

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

    // ============================================================
    // IMPORTACIÓN DE ALUMNOS
    // ============================================================

    /**
     * Importa alumnos desde archivo Excel
     *
     * Estructura esperada:
     * DNI ALUMNO | EMAIL ALUMNO | NOMBRE ALUMNO | APELLIDO1 ALUMNO | APELLIDO2 ALUMNO | MATRICULA ALUMNO | GRUPO
     */
    public function importar(UploadedFile $file): array {
        $extension = $file->getClientOriginalExtension();
        $data = [];
        $header = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Lógica para EXCEL
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $header = array_shift($rows);
            foreach ($rows as $row) {
                // Saltar filas vacías
                if (empty($row[0]) || $row[0] === null) {
                    continue;
                }

                // Verificar que hay suficientes columnas
                if (count($row) >= count($header)) {
                    $data[] = array_combine($header, array_slice($row, 0, count($header)));
                }
            }
        } else {
            // Lógica para CSV
            $content = file_get_contents($file->getRealPath());
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            $delimiter = str_contains($content, ';') ? ';' : ',';
            $lines = explode("\n", str_replace("\r", "", trim($content)));
            $header = str_getcsv(array_shift($lines), $delimiter);
            $header[0] = preg_replace('/^[\xEF\xBB\xBF\xFF\xFE]/', '', $header[0]);

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                $fields = str_getcsv($line, $delimiter);
                if (count($header) == count($fields)) {
                    $data[] = array_combine($header, $fields);
                }
            }
        }

        return DB::transaction(function () use ($data, $header) {
            if (in_array('DNI ALUMNO', $header)) {
                return $this->procesarAlumnos($data);
            }
            if (in_array('Alias_Profesor', $header)) {
                return $this->procesarAsignaciones($data);
            }
            throw new Exception("Formato de columnas no reconocido. Se esperaba 'DNI ALUMNO' o 'Alias_Profesor' en el encabezado.");
        });
    }

    /**
     * Procesa los datos de alumnos
     */
    private function procesarAlumnos(array $data): array {
        $stats = [
            'usuarios_creados' => 0,
            'usuarios_actualizados' => 0,
            'alumnos_creados' => 0,
            'alumnos_actualizados' => 0,
            'filas_procesadas' => 0,
            'errores' => []
        ];

        foreach ($data as $index => $row) {
            try {
                // Validar campos obligatorios
                if (empty($row['DNI ALUMNO']) || empty($row['EMAIL ALUMNO'])) {
                    $stats['errores'][] = "Fila " . ($index + 2) . ": DNI o EMAIL vacío";
                    continue;
                }

                $dni = trim($row['DNI ALUMNO']);
                $email = trim($row['EMAIL ALUMNO']);
                $nombre = trim($row['NOMBRE ALUMNO'] ?? '');
                $apellido1 = trim($row['APELLIDO1 ALUMNO'] ?? '');
                $apellido2 = trim($row['APELLIDO2 ALUMNO'] ?? '');
                $matricula = trim($row['MATRICULA ALUMNO'] ?? '');
                $grupo = trim($row['GRUPO'] ?? null);

                // 1. Crear o actualizar Usuario
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'password' => Hash::make($dni),
                        'role' => 'alumno'
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $stats['usuarios_creados']++;
                } else {
                    $stats['usuarios_actualizados']++;
                }

                // 2. Crear o actualizar Alumno
                $apellidos = trim($apellido1 . ' ' . $apellido2);

                $alumno = Alumnos::updateOrCreate(
                    ['dni' => $dni],
                    [
                        'user_id' => $user->id,
                        'nombre' => $nombre,
                        'apellidos' => $apellidos,
                        'matricula_id' => $matricula,
                        'grupo' => $grupo
                    ]
                );

                if ($alumno->wasRecentlyCreated) {
                    $stats['alumnos_creados']++;
                } else {
                    $stats['alumnos_actualizados']++;
                }

                $stats['filas_procesadas']++;

            } catch (Exception $e) {
                $stats['errores'][] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        return $stats;
    }
}
