<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ciclos;
use App\Models\Alumnos;
use App\Models\Empresas;
use App\Models\Estancia;
use App\Models\TutorEgibide;
use App\Models\TutorEmpresa;
use App\Models\EntregaCuaderno;
use App\Models\AlumnoEntrega;
use App\Models\Asignatura;
use App\Models\CompetenciaTec;
use App\Models\CompetenciaTransversal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper para crear usuario con rol y perfil asociado.
     */
    protected function getAuthHeaderFor(string $role, $extraAttributes = [])
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'email' => $role . '_' . uniqid() . '@test.com',
        ], $extraAttributes));

        if ($role === 'alumno') {
            Alumnos::factory()->create(['user_id' => $user->id]);
        } elseif ($role === 'tutor_egibide') {
            TutorEgibide::factory()->create(['user_id' => $user->id]);
        } elseif ($role === 'tutor_empresa') {
            TutorEmpresa::factory()->create(['user_id' => $user->id]);
        }

        $token = $user->createToken('test-token')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    // ==========================================
    // TESTS DE LECTURA (GET) - YA FUNCIONAN
    // ==========================================

    public function test_user_endpoint_returns_authenticated_user()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/user');
        $response->assertStatus(200);
    }

    public function test_familias_profesionales_can_be_listed()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/familiasProfesionales');
        $response->assertStatus(200);
    }

    public function test_ciclos_can_be_listed()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/ciclos');
        $response->assertStatus(200);
    }

    public function test_ciclos_plantilla_can_be_downloaded()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get('/api/ciclos/plantilla');
        $response->assertStatus(200);
    }

    public function test_get_tutores_by_ciclo()
    {
        $ciclo = Ciclos::factory()->create();
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get("/api/ciclo/{$ciclo->id}/tutores");
        $response->assertStatus(200);
    }

    public function test_get_asignaturas_by_ciclo()
    {
        $ciclo = Ciclos::factory()->create();
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get("/api/ciclo/{$ciclo->id}/asignaturas");
        $response->assertStatus(200);
    }

    public function test_admin_ciclo_show()
    {
        $ciclo = Ciclos::factory()->create();
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get("/api/admin/ciclos/{$ciclo->id}");
        $response->assertStatus(200);
    }

    public function test_competencias_can_be_listed()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get('/api/competencias');
        $response->assertStatus(200);
    }

    public function test_get_competencias_tecnicas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/competenciasTecnicas/alumno/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_get_competencias_tecnicas_asignadas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/competenciasTecnicas/alumno/{$alumno->id}/asignadas");
        $response->assertStatus(200);
    }

    public function test_get_competencias_transversales_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/competenciasTransversales/alumno/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_get_calificaciones_competencias_tecnicas()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/competenciasTecnicas/calificaciones/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_get_calificaciones_competencias_transversales()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/competenciasTransversales/calificaciones/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_get_notas_tecnicas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/notas/alumno/{$alumno->id}/tecnicas");
        $response->assertStatus(200);
    }

    public function test_get_notas_transversales_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $response = $this->withHeaders($headers)->get("/api/notas/alumno/{$alumno->id}/transversal");
        $response->assertStatus(200);
    }

    public function test_get_notas_egibide_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/notas/alumno/{$alumno->id}/egibide");
        $response->assertStatus(200);
    }

    public function test_get_nota_cuaderno_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/notas/alumno/{$alumno->id}/cuaderno");
        $response->assertStatus(200);
    }

    public function test_empresas_can_be_listed()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get('/api/empresas');
        $response->assertStatus(200);
    }

    public function test_mi_empresa_alumno()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/me/empresa');
        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_admin_detalle_empresa()
    {
        $empresa = Empresas::factory()->create();
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get("/api/admin/empresas/{$empresa->id}");
        $response->assertStatus(200);
    }

    public function test_inicio_alumno()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/me/inicio');
        $response->assertStatus(200);
    }

    public function test_alumnos_list_as_tutor()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get('/api/alumnos');
        $response->assertStatus(200);
    }

    public function test_me_alumno()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/me/alumno');
        $response->assertStatus(200);
    }

    public function test_get_asignaturas_alumno_propias()
    {
        $user = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->withHeaders($headers)->get("/api/alumnos/{$alumno->id}/asignaturas");
        $response->assertStatus(200);
    }

    public function test_admin_detalle_alumno()
    {
        $alumno = Alumnos::factory()->create();
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get("/api/admin/alumnos/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_get_entregas_mias()
    {
        $headers = $this->getAuthHeaderFor('alumno');
        $response = $this->withHeaders($headers)->get('/api/entregas/mias');
        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_get_entregas_alumno_como_tutor()
    {
        $alumno = Alumnos::factory()->create();
        $tutorUser = User::factory()->create(['role' => 'tutor_egibide']);
        $tutor = TutorEgibide::factory()->create(['user_id' => $tutorUser->id]);
        Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $tarea = EntregaCuaderno::create([
            'tutor_id' => $tutor->id,
            'descripcion' => 'Tarea de prueba',
            'fecha_creacion' => now(),
            'fecha_limite' => now()->addDays(7),
        ]);

        AlumnoEntrega::create([
            'alumno_id' => $alumno->id,
            'entrega_id' => $tarea->id,
            'url_entrega' => 'http://test.com/archivo.pdf',
            'fecha_entrega' => now(),
            'observaciones' => 'Entrega enviada',
        ]);

        $token = $tutorUser->createToken('test')->plainTextToken;
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->get("/api/alumnos/{$alumno->id}/entregas");

        $response->assertStatus(200);
    }

    public function test_inicio_tutor_egibide()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get('/api/tutorEgibide/inicio');
        $response->assertStatus(200);
    }

    public function test_me_tutor_egibide()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get('/api/me/tutor-egibide');
        $response->assertStatus(200);
    }

    public function test_get_detalle_empresa_tutor()
    {
        $empresa = Empresas::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/tutorEgibide/empresa/{$empresa->id}");
        $this->assertTrue(in_array($response->status(), [200, 404]));
    }

    public function test_get_mis_cursos_con_alumnos_sin_tutor()
    {
        $user = User::factory()->create(['role' => 'tutor_egibide']);
        $tutor = TutorEgibide::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->withHeaders($headers)->get("/api/tutorEgibide/{$tutor->id}/cursos/alumnos");
        $response->assertStatus(200);
    }

    public function test_inicio_instructor()
    {
        $headers = $this->getAuthHeaderFor('tutor_empresa');
        $response = $this->withHeaders($headers)->get('/api/tutorEmpresa/inicio');
        $response->assertStatus(200);
    }

    public function test_me_tutor_empresa()
    {
        $headers = $this->getAuthHeaderFor('tutor_empresa');
        $response = $this->withHeaders($headers)->get('/api/me/tutor-empresa');
        $response->assertStatus(200);
    }

    public function test_get_seguimientos_alumno()
    {
        $alumno = Alumnos::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/seguimientos/alumno/{$alumno->id}");
        $response->assertStatus(200);
    }

    public function test_admin_inicio()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get('/api/admin/inicio');
        $response->assertStatus(200);
    }

    public function test_estancias_can_be_listed()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get('/api/estancias');
        $response->assertStatus(200);
    }

    public function test_estancia_show()
    {
        $estancia = Estancia::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/estancias/{$estancia->id}");
        $response->assertStatus(200);
    }

    public function test_get_estancias_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/alumnos/{$alumno->id}/estancias");
        $response->assertStatus(200);
    }

    public function test_get_matriz_competencias()
    {
        $ciclo = Ciclos::factory()->create();
        $headers = $this->getAuthHeaderFor('admin');
        $response = $this->withHeaders($headers)->get("/api/ciclo/{$ciclo->id}/matriz-competencias");
        $response->assertStatus(200);
    }

    public function test_get_horario()
    {
        $estancia = Estancia::factory()->create();
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $response = $this->withHeaders($headers)->get("/api/horario/{$estancia->id}");
        $response->assertStatus(200);
    }

    // =========================================================================
    // TESTS DE ESCRITURA (POST / PUT / DELETE)
    // =========================================================================

    // --- ADMIN CREATION ---

    public function test_admin_can_create_ciclo()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $data = Ciclos::factory()->make()->toArray();
        // Asegúrate de enviar los campos obligatorios que espera el controlador
        $response = $this->withHeaders($headers)->post('/api/ciclos', $data);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_admin_can_create_empresa()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $data = Empresas::factory()->make()->toArray();
        $response = $this->withHeaders($headers)->post('/api/empresas', $data);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_admin_can_create_alumno()
    {
        $headers = $this->getAuthHeaderFor('admin');

        // 1. Necesitamos un Grado existente. 
        // Asumo que tienes el modelo App\Models\Grado. Si no, ajusta el nombre.
        $grado = Ciclos::factory()->create();

        $user = User::factory()->make(['role' => 'alumno']);

        $payload = [
            'nombre' => "Prueba",
            'apellidos' => "auigvgf",
            'curso' => $grado->grupo,
            'matricula' => 12340,
            'dni' => '12345678Z', // Campo suele ser obligatorio
            'ciudad' => 'Calle Prueba 123',
            'telefono' => '600123456',
            'curso' => "DASDA",
            "tutor" => 1
        ];

        $response = $this->withHeaders($headers)->post('/api/alumnos', $payload);

        // Si falla, esto te dirá qué campo falta
        if ($response->status() !== 200 && $response->status() !== 201) {
            dump("Error creando alumno:", $response->json());
        }

        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_admin_can_create_estancia()
    {
        $headers = $this->getAuthHeaderFor('admin');
        $alumno = Alumnos::factory()->create();
        $empresa = Empresas::factory()->create();

        $payload = [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id,
            'fecha_inicio' => now()->format('Y-m-d'),
            'fecha_fin' => now()->addMonths(3)->format('Y-m-d'),
        ];

        $response = $this->withHeaders($headers)->post('/api/estancias', $payload);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    // --- TUTOR ACTIONS (ASIGNACIÓN) ---

    public function test_tutor_can_assign_alumno()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $alumno = Alumnos::factory()->create();
        $tutor = TutorEgibide::factory()->create();
        // CORRECCIÓN: ID_Alumno
        $payload = ['alumno_id' => $alumno->id, 'tutor_id' => $tutor->id];

        $response = $this->withHeaders($headers)->post('/api/tutorEgibide/asignarAlumno', $payload);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_tutor_can_assign_instructor()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $empresa = Empresas::factory()->create();
        $user = User::factory()->create();

        $payload = [
            'empresa_id' => $empresa->id,
            'nombre' => 'Juan',
            'apellidos' => 'Perez',
            'telefono' => '123456789',
            'ciudad' => 'Bilbao',
            'user_id' => $user->id

        ];

        $response = $this->withHeaders($headers)
            ->postJson('/api/tutorEgibide/asignar-instructor', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('instructores', [
            'empresa_id' => $empresa->id,
            'nombre' => 'Juan',
            'apellidos' => 'Perez',
        ]);
    }


    public function test_tutor_can_assign_empresa()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $alumno = Alumnos::factory()->create();
        $empresa = Empresas::factory()->create();


        // CORRECCIÓN: Revisa si tu tabla usa 'id' o 'CIF'. 
        // Según tu modelo EstanciaAlumno es 'CIF_Empresa'.
        $payload = [
            'alumno_id' => $alumno->id,
            'empresa_id' => $empresa->id, // O 'CIF_Empresa' => $empresa->CIF si usas CIF
        ];

        $response = $this->withHeaders($headers)->post('/api/empresas/asignar', $payload);
        $this->assertTrue(in_array($response->status(), [200, 201, 404]));
    }

    // --- SEGUIMIENTOS Y NOTAS ---

    public function test_tutor_can_create_seguimiento()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]); // Necesario para asociar seguimiento

        $payload = [
            'alumno_id' => $alumno->id,
            'fecha' => now()->format('Y-m-d'),
            'accion' => 'Reunión',
            'descripcion' => 'Todo correcto',
            'via' => null
        ];

        $response = $this->withHeaders($headers)->post('/api/nuevo-seguimiento', $payload);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_tutor_can_grade_competencias_tecnicas()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $alumno = Alumnos::factory()->create();
        Estancia::factory()->create(['alumno_id' => $alumno->id]);
        $competencia = CompetenciaTec::factory()->create();
        $payload = [
            'alumno_id' => $alumno->id,
            'competencias' => [
                ['competencia_id' => $competencia->id, 'calificacion' => 4]
            ]
        ];

        $response = $this->withHeaders($headers)->post('/api/competenciasTecnicas/calificar', $payload);
        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    public function test_tutor_can_grade_competencias_transversales()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $alumno = Alumnos::factory()->create();

        $estancia = Estancia::factory()->create([
            'alumno_id' => $alumno->id
        ]);

        $competencia1 = CompetenciaTransversal::factory()->create();
        $competencia2 = CompetenciaTransversal::factory()->create();

        $payload = [
            'alumno_id' => $alumno->id,
            'competencias' => [
                [
                    'competencia_id' => $competencia1->id,
                    'calificacion' => 3
                ],
                [
                    'competencia_id' => $competencia2->id,
                    'calificacion' => 4
                ]
            ]
        ];

        $response = $this->withHeaders($headers)
            ->postJson('/api/competenciasTransversales/calificar', $payload);

        $response->assertStatus(201);
    }


    public function test_tutor_can_save_notas_egibide()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');

        $alumno = Alumnos::factory()->create();
        $asignatura = Asignatura::factory()->create();

        $payload = [
            'alumno_id' => $alumno->id,
            'asignatura_id' => $asignatura->id,
            'nota' => 9
        ];

        $response = $this->withHeaders($headers)
            ->postJson('/api/notas/alumno/egibide/guardar', $payload);

        $response->assertStatus(201);
    }


    // --- ALUMNO (SUBIDA DE ARCHIVOS) ---

    public function test_alumno_can_upload_entrega()
    {
        Storage::fake('local');

        // 1. Crear usuario alumno
        $user = User::factory()->create(['role' => 'alumno']);
        $alumno = Alumnos::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        // 2. Crear un Tutor para asignarlo a la entrega (SOLUCIÓN AL ERROR SQL)
        $tutorUser = User::factory()->create(['role' => 'tutor_egibide']);
        $tutor = TutorEgibide::factory()->create(['user_id' => $tutorUser->id]);

        // 3. Crear Estancia
        Estancia::factory()->create(['alumno_id' => $alumno->id]);

        // 4. Crear la Tarea (Asignando el tutor_id obligatorio)
        $tarea = EntregaCuaderno::create([
            'fecha_creacion' => now(),
            'fecha_limite' => now()->addDays(5),
            'descripcion' => 'Tarea test',
            'tutor_id' => $tutor->id // <--- ESTO FALTABA
        ]);

        $file = UploadedFile::fake()->create('trabajo.pdf', 100);

        // 5. Enviar petición
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post("/api/alumno/{$alumno->id}/entrega", [
                'archivo' => $file,
                'entrega_id' => $tarea->id,
                'observaciones' => 'Mi entrega'
            ]);

        // Si falla, muestra el error en consola para depurar
        if ($response->status() !== 200 && $response->status() !== 201) {
            dump($response->json());
        }

        $this->assertTrue(in_array($response->status(), [200, 201]));
    }

    // --- HORARIOS ---

    public function test_tutor_can_assign_horario()
    {
        $headers = $this->getAuthHeaderFor('tutor_egibide');
        $estancia = Estancia::factory()->create();

        $payload = [
            'estancia_id' => $estancia->id,
            'horarios' => [
                [
                    'dia_semana' => 'Lunes',
                    'tramos' => [
                        [
                            'hora_inicio' => '08:00',
                            'hora_fin' => '14:00'
                        ]
                    ]
                ],
                [
                    'dia_semana' => 'Martes',
                    'tramos' => [
                        [
                            'hora_inicio' => '08:00',
                            'hora_fin' => '14:00'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->withHeaders($headers)
            ->postJson('/api/horario/asignar', $payload);

        $response->assertStatus(201);
    }
}
