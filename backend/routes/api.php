<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiclosController;
use App\Http\Controllers\CompetenciasController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\AlumnosController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\FamiliaProfesionalController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\TutorEgibideController;
use App\Http\Controllers\TutorEmpresaController;
use App\Http\Controllers\SeguimientosController;
use App\Http\Controllers\EntregaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlumnoEntregaController;
use App\Http\Controllers\EstanciaController;
use App\Http\Controllers\CompetenciaRaController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\HorarioController;

// Rutas públicas
Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::get('/entregas/{entrega}/archivo', [EntregaController::class, 'archivo']);

Route::middleware('auth:sanctum')->group(function () {

    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ========================================
    // RUTAS EXCLUSIVAS DE ADMIN
    // ========================================

    Route::middleware('can:is-admin')->group(function () {

        // --- ESTAS SON LAS RUTAS QUE TE FALTABAN ---
        Route::get('/admin/inicio', [AdminController::class, 'inicioAdmin']);
        Route::get('/admin/alumnos/{id}', [AdminController::class, 'detalleAlumno']);
        Route::get('/admin/empresas/{empresaId}', [AdminController::class, 'detalleEmpresa']);

        Route::post('/importar-alumnos', [ImportacionController::class, 'upload']);
        Route::post('/importar-asignaciones', [ImportacionController::class, 'uploadAsignaciones']);
        // -------------------------------------------

        // Ciclos - operaciones administrativas
        Route::post('/ciclos', [CiclosController::class, 'store']);
        Route::post('/ciclos/importar', [CiclosController::class, 'importarCSV']);
        Route::get('/ciclos/plantilla', [CiclosController::class, 'descargarPlantillaCSV']);
      Route::get('/admin/ciclos/{ciclo}', [CiclosController::class, 'show']);
       Route::get('/ciclo/{ciclo_id}/tutores', [TutorEgibideController::class, 'getTutoresByCiclo']);
        Route::get('/ciclo/{ciclo_id}/asignaturas', [CiclosController::class, 'getAsignaturasByCiclo']);

        // Competencias
        Route::post('/competencia/tecnica', [CompetenciasController::class, 'storeTecnica']);
        Route::post('/competencia/transversal', [CompetenciasController::class, 'storeTransversal']);

        // Empresas y Alumnos
        Route::post('/empresas', [EmpresasController::class, 'store']);
        Route::post('/alumnos', [AlumnosController::class, 'store']);

        // Matriz competencias
        Route::get('/ciclo/{id}/matriz-competencias', [CompetenciaRaController::class, 'getCompRa']);

        Route::post('/competencia-tec-ra/toggle', [CompetenciaRaController::class, 'createOrDelete']);
        Route::post('/competenciasTecnicas/asignar-ra', [CompetenciaRaController::class, 'createOrDelete']);
    });

    // ========================================
    // RUTAS EXCLUSIVAS DE ALUMNO
    // ========================================
    Route::middleware('can:is-alumno')->group(function () {
        Route::get('/me/inicio', [AlumnosController::class, 'inicio']);
        Route::get('/me/alumno', [AlumnosController::class, 'me']);
        Route::get('/me/empresa', [EmpresasController::class, 'miEmpresa']);
        Route::post('/alumno/{alumnoId}/entrega', [AlumnoEntregaController::class, 'storeEntrega']);
        Route::get('/entregas/mias', [EntregaController::class, 'mias']);
    });

    // ========================================
    // RUTAS EXCLUSIVAS DE TUTOR EGIBIDE
    // ========================================
    Route::middleware('can:is-tutor-egibide')->group(function () {
        Route::get('/tutorEgibide/inicio', [TutorEgibideController::class, 'inicioTutor']);
        Route::get('/me/tutor-egibide', [TutorEgibideController::class, 'me']);
        Route::get('/tutorEgibide/{tutorId}/alumnos', [TutorEgibideController::class, 'getAlumnosByCurrentTutor']);
        Route::get('/tutorEgibide/{tutorId}/entregas', [TutorEgibideController::class, 'getEntregasByCurrentTutor']);
        Route::get('/tutorEgibide/{tutorId}/empresas', [TutorEgibideController::class, 'conseguirEmpresasporTutor']);
        Route::get('/tutorEgibide/empresa/{empresaId}', [TutorEgibideController::class, 'getDetalleEmpresa']);
        Route::get('/tutorEgibide/{tutorId}/cursos/alumnos', [TutorEgibideController::class, 'getMisCursosConAlumnosSinTutor']);
        Route::get('/tutorEgibide/todas-empresas', [TutorEgibideController::class, 'conseguirTodasLasEmpresas']);

        Route::post('/tutorEgibide/asignarAlumno', [TutorEgibideController::class, 'asignarAlumno']);
        Route::post('/tutorEgibide/asignar-instructor', [TutorEgibideController::class, 'asignarInstructor']);
        Route::post('/horasperiodo', [TutorEgibideController::class, 'horasperiodo']);
        Route::post('/empresas/asignar', [EmpresasController::class, 'storeEmpresaAsignada']);
        Route::post('/nuevo-seguimiento', [SeguimientosController::class, 'nuevoSeguimiento']);
        Route::delete('/seguimientos/{seguimiento}', [SeguimientosController::class, 'destroy']);

        Route::post('/entregas', [EntregaController::class, 'store']);
        Route::delete('/entregas/{id}', [EntregaController::class, 'destroy']);
        Route::put('/cuaderno/{idEntrega}/', [AlumnoEntregaController::class, 'actualizar']);

        // Competencias - asignar y calificar
        Route::post('/competenciasTecnicas/asignar', [CompetenciasController::class, 'storeCompetenciasTecnicasAsignadas']);
        Route::post('/competenciasTecnicas/calificar', [CompetenciasController::class, 'storeCompetenciasTecnicasCalificadas']);
        Route::post('/competenciasTransversales/calificar', [CompetenciasController::class, 'storeCompetenciasTransversalesCalificadas']);

        // Notas
        Route::post('/notas/alumno/egibide/guardar', [NotasController::class, 'guardarNotasEgibide']);
        Route::post('/notas/alumno/cuaderno/guardar', [NotasController::class, 'guardarNotasCuaderno']);
    });

    // ========================================
    // RUTAS EXCLUSIVAS DE TUTOR EMPRESA
    // ========================================
    Route::middleware('can:is-tutor-empresa')->group(function () {
        Route::get('/tutorEmpresa/inicio', [TutorEmpresaController::class, 'inicioInstructor']);
        Route::get('/me/tutor-empresa', [TutorEmpresaController::class, 'me']);
        Route::get('/tutorEmpresa/{tutorId}/alumnos', [TutorEmpresaController::class, 'getAlumnosByCurrentInstructor']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: ver-alumnos (tutor_egibide, tutor_empresa, admin)
    // ========================================
    Route::middleware('can:ver-alumnos')->group(function () {
        Route::get('/alumnos', [AlumnosController::class, 'index']);
        Route::get('/alumnos/{alumno_id}/asignaturas', [AlumnosController::class, 'getAsignaturasAlumno']);
        Route::get('/alumnos/{alumno}/entregas', [AlumnosController::class, 'entregas']);
    });

    Route::middleware('can:gestionar-competencias')->group(function () {
                Route::post('/competenciasTecnicas/asignar', [CompetenciasController::class, 'storeCompetenciasTecnicasAsignadas']);
        Route::post('/competenciasTecnicas/calificar', [CompetenciasController::class, 'storeCompetenciasTecnicasCalificadas']);
        Route::post('/competenciasTransversales/calificar', [CompetenciasController::class, 'storeCompetenciasTransversalesCalificadas']);

    });

    // ========================================
    // RUTAS COMPARTIDAS: ver-competencias (tutor_egibide, tutor_empresa, admin)
    // ========================================
    Route::middleware('can:ver-competencias')->group(function () {
        Route::get('/competencias', [CompetenciasController::class, 'index']);
        Route::get('/competenciasTecnicas/alumno/{alumno_id}', [CompetenciasController::class, 'getCompetenciasTecnicasByAlumno']);
        Route::get('/competenciasTecnicas/alumno/{alumno_id}/asignadas', [CompetenciasController::class, 'getCompetenciasTecnicasAsignadasByAlumno']);
        Route::get('/competenciasTransversales/alumno/{alumno_id}', [CompetenciasController::class, 'getCompetenciasTransversalesByAlumno']);
        Route::get('/competenciasTecnicas/calificaciones/{alumno_id}', [CompetenciasController::class, 'getCalificacionesCompetenciasTecnicas']);
        Route::get('/competenciasTransversales/calificaciones/{alumno_id}', [CompetenciasController::class, 'getCalificacionesCompetenciasTransversales']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: gestionar-notas (tutor_egibide, tutor_empresa)
    // ========================================
    Route::middleware('can:gestionar-notas')->group(function () {
        Route::get('/notas/alumno/{alumno_id}/tecnicas', [NotasController::class, 'obtenerNotasTecnicas']);
        Route::get('/notas/alumno/{alumno_id}/transversal', [NotasController::class, 'obtenerNotasTransversales']);
        Route::get('/notas/alumno/{alumno_id}/egibide', [NotasController::class, 'obtenerNotasEgibide']);
        Route::get('/notas/alumno/{alumno_id}/cuaderno', [NotasController::class, 'obtenerNotaCuadernoByAlumno']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: ver-seguimientos (alumno, tutor_egibide)
    // ========================================
    Route::middleware('can:ver-seguimientos')->group(function () {
        Route::get('/seguimientos/alumno/{alumno_Id}', [SeguimientosController::class, 'seguimientosAlumno']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: ver-empresas (alumno, tutor_egibide, admin)
    // ========================================
    Route::middleware('can:ver-empresas')->group(function () {
        Route::get('/empresas', [EmpresasController::class, 'index']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: gestionar-estancias (tutor_egibide, admin)
    // ========================================
    Route::middleware('can:gestionar-estancias')->group(function () {
        Route::get('/estancias', [EstanciaController::class, 'index']);
        Route::post('/estancias', [EstanciaController::class, 'store']);
        Route::get('/estancias/{id}', [EstanciaController::class, 'show']);
        Route::put('/estancias/{id}', [EstanciaController::class, 'update']);
        Route::delete('/estancias/{id}', [EstanciaController::class, 'destroy']);
        Route::get('/alumnos/{alumno_id}/estancias', [EstanciaController::class, 'getEstanciasByAlumno']);
    });

    // ========================================
    // RUTAS COMPARTIDAS: gestionar-horarios (tutor_egibide, admin)
    // ========================================
    Route::middleware('can:gestionar-horarios')->group(function () {
        Route::post('/horario/asignar', [HorarioController::class, 'asignarHorario']);
        Route::get('/horario/{idEstancia}', [HorarioController::class, 'getHorario']);

        // Instructores
        Route::get('/instructores', [InstructorController::class, 'index']);
        Route::post('/instructores', [InstructorController::class, 'store']);
        Route::get('/instructores/{id}', [InstructorController::class, 'show']);
        Route::put('/instructores/{id}', [InstructorController::class, 'update']);
        Route::delete('/instructores/{id}', [InstructorController::class, 'destroy']);
    });

    // ========================================
    // RUTAS GENERALES (acceso autenticado básico)
    // ========================================

    // Familias Profesionales - acceso general
    Route::get('/familiasProfesionales', [FamiliaProfesionalController::class, 'index']);

    // Ciclos - consulta general
    Route::get('/ciclos', [CiclosController::class, 'index']);
    Route::get('/ciclo/{ciclo_id}/cursos', [CiclosController::class, 'getCursosByCiclos']);
    Route::get('/ciclo/{ciclo_id}/tutores', [TutorEgibideController::class, 'getTutoresByCiclo']);
    Route::get('/ciclo/{ciclo_id}/asignaturas', [CiclosController::class, 'getAsignaturasByCiclo']);
});
