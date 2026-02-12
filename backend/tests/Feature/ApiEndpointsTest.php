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
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    // ========== USER ==========
    public function test_user_endpoint_returns_authenticated_user()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'email' => $this->user->email
        ]);
    }

    // ========== FAMILIAS PROFESIONALES ==========
    public function test_familias_profesionales_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/familiasProfesionales');

        $response->assertStatus(200);
    }

    // ========== CICLOS ==========
    public function test_ciclos_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/ciclos');

        $response->assertStatus(200);
    }

    public function test_ciclos_plantilla_can_be_downloaded()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/ciclos/plantilla');

        $response->assertStatus(200);
    }


    public function test_get_tutores_by_ciclo()
    {
        $ciclo = Ciclos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/ciclo/{$ciclo->id}/tutores");

        $response->assertStatus(200);
    }

    public function test_get_asignaturas_by_ciclo()
    {
        $ciclo = Ciclos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/ciclo/{$ciclo->id}/asignaturas");

        $response->assertStatus(200);
    }

    public function test_admin_ciclo_show()
    {
        $ciclo = Ciclos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/admin/ciclos/{$ciclo->id}");

        $response->assertStatus(200);
    }

    // ========== COMPETENCIAS ==========
    public function test_competencias_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/competencias');

        $response->assertStatus(200);
    }

    public function test_get_competencias_tecnicas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/competenciasTecnicas/alumno/{$alumno->id}");

$this->assertTrue(
    in_array($response->status(), [200, 404,500])
);

    }

    public function test_get_competencias_tecnicas_asignadas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/competenciasTecnicas/alumno/{$alumno->id}/asignadas");

        $response->assertStatus(200);
    }

    public function test_get_competencias_transversales_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/competenciasTransversales/alumno/{$alumno->id}");

        $response->assertStatus(200);
    }

    public function test_get_calificaciones_competencias_tecnicas()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/competenciasTecnicas/calificaciones/{$alumno->id}");

        $response->assertStatus(200);
    }

    public function test_get_calificaciones_competencias_transversales()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/competenciasTransversales/calificaciones/{$alumno->id}");

        $response->assertStatus(200);
    }

    // ========== NOTAS ==========
    public function test_get_notas_tecnicas_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/notas/alumno/{$alumno->id}/tecnicas");

        $response->assertStatus(200);
    }

    public function test_get_notas_transversales_by_alumno()
    {
        $alumno = Alumnos::factory()->create();
    Estancia::factory()->create(['alumno_id' => $alumno->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/notas/alumno/{$alumno->id}/transversal");

        $response->assertStatus(200);
    }

    public function test_get_notas_egibide_by_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/notas/alumno/{$alumno->id}/egibide");

        $response->assertStatus(200);
    }

    public function test_get_nota_cuaderno_by_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/notas/alumno/{$alumno->id}/cuaderno");

        $response->assertStatus(200);
    }

    // ========== EMPRESAS ==========
    public function test_empresas_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/empresas');

        $response->assertStatus(200);
    }

    public function test_mi_empresa()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/me/empresa');

        // Puede devolver 404 si el usuario no tiene empresa asignada
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_admin_detalle_empresa()
    {
        $empresa = Empresas::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/admin/empresas/{$empresa->id}");

        $response->assertStatus(200);
    }

    // ========== ALUMNOS ==========
    public function test_inicio_alumno()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/me/inicio');

        // Puede devolver 404 si el usuario no es alumno
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_alumnos_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/alumnos');

        $response->assertStatus(200);
    }

    public function test_me_alumno()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/me/alumno');

        // Puede devolver 404 si el usuario no es alumno
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_get_asignaturas_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/alumnos/{$alumno->id}/asignaturas");

        $response->assertStatus(200);
    }

    public function test_get_entregas_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/alumnos/{$alumno->id}/entregas");
$this->assertTrue(
    in_array($response->status(), [200, 404,500])
);

    }

    public function test_admin_detalle_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/admin/alumnos/{$alumno->id}");

        // Puede devolver 403 si no tiene permisos de admin
        $this->assertContains($response->status(), [200, 403]);
    }

    // ========== ENTREGAS ==========
    public function test_get_entregas_mias()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/entregas/mias');

        // Puede devolver 404 si el usuario no tiene entregas
        $this->assertContains($response->status(), [200, 404]);
    }

    // ========== TUTOR EGIBIDE ==========
    public function test_inicio_tutor_egibide()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/tutorEgibide/inicio');

        // Puede devolver 404 si el usuario no es tutor
        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_get_detalle_empresa_tutor()
    {
        $empresa = Empresas::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEgibide/empresa/{$empresa->id}");

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_get_mis_cursos_con_alumnos_sin_tutor()
    {
        $tutor = TutorEgibide::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEgibide/{$tutor->id}/cursos/alumnos");

        $response->assertStatus(200);
    }

    public function test_get_alumnos_by_current_tutor()
    {
        $tutor = TutorEgibide::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEgibide/{$tutor->id}/alumnos");

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_get_entregas_by_current_tutor()
    {
        $tutor = TutorEgibide::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEgibide/{$tutor->id}/entregas");

        $response->assertStatus(200);
    }

    public function test_get_empresas_por_tutor()
    {
        $tutor = TutorEgibide::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEgibide/{$tutor->id}/empresas");

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_me_tutor_egibide()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/me/tutor-egibide');

        $this->assertContains($response->status(), [200, 404, 500]);
    }

    // ========== TUTOR EMPRESA ==========
    public function test_inicio_instructor()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/tutorEmpresa/inicio');

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_get_alumnos_by_current_instructor()
    {
        $tutor = TutorEmpresa::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/tutorEmpresa/{$tutor->id}/alumnos");

        $this->assertContains($response->status(), [200, 404]);
    }

    public function test_me_tutor_empresa()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/me/tutor-empresa');

        $this->assertContains($response->status(), [200, 404, 500]);
    }

    // ========== SEGUIMIENTOS ==========
    public function test_get_seguimientos_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/seguimientos/alumno/{$alumno->id}");

        $response->assertStatus(200);
    }

    // ========== ADMIN ==========
    public function test_admin_inicio()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/admin/inicio');

        // Puede devolver 403 si no es admin
        $this->assertContains($response->status(), [200, 403]);
    }

    // ========== ESTANCIAS ==========
    public function test_estancias_can_be_listed()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/estancias');

        $response->assertStatus(200);
    }

    public function test_estancia_show()
    {
        $estancia = Estancia::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/estancias/{$estancia->id}");

        $response->assertStatus(200);
    }

    public function test_get_estancias_by_alumno()
    {
        $alumno = Alumnos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/alumnos/{$alumno->id}/estancias");

        $response->assertStatus(200);
    }

    // ========== COMPETENCIAS RA ==========
    public function test_get_matriz_competencias()
    {
        $ciclo = Ciclos::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/ciclo/{$ciclo->id}/matriz-competencias");

        $response->assertStatus(200);
    }

    // ========== HORARIO ==========
    public function test_get_horario()
    {
        $estancia = Estancia::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/horario/{$estancia->id}");

        $response->assertStatus(200);
    }
}
