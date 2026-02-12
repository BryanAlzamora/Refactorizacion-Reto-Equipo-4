<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ========================================
        // GATES GENERALES DE ROLES
        // ========================================
        
        Gate::define('is-alumno', function (User $user) {
            return $user->role === 'alumno';
        });

        Gate::define('is-tutor-egibide', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('is-tutor-empresa', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // ========================================
        // GATES PARA ALUMNOS
        // ========================================
        
        Gate::define('alumno.ver-inicio', function (User $user) {
            return $user->role === 'alumno';
        });

        Gate::define('alumno.ver-mis-datos', function (User $user) {
            return $user->role === 'alumno';
        });

        Gate::define('alumno.ver-empresa', function (User $user) {
            return $user->role === 'alumno';
        });

        Gate::define('alumno.ver-seguimiento', function (User $user) {
            return $user->role === 'alumno';
        });

        Gate::define('alumno.ver-calificacion', function (User $user, $alumnoId) {
            return $user->role === 'alumno' && $user->id == $alumnoId;
        });

        Gate::define('alumno.gestionar-entregas', function (User $user) {
            return $user->role === 'alumno';
        });

        // ========================================
        // GATES PARA TUTOR EGIBIDE
        // ========================================
        
        Gate::define('tutor-egibide.ver-inicio', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.ver-grados', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.ver-alumnos', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.ver-entregas', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.asignar-empresa', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.asignar-horas-periodo', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.ver-seguimiento', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.crear-seguimiento', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.gestionar-competencias', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.gestionar-calificaciones', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.ver-empresas', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.asignar-alumno', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        Gate::define('tutor-egibide.asignar-instructor', function (User $user) {
            return $user->role === 'tutor_egibide';
        });

        // ========================================
        // GATES PARA TUTOR EMPRESA
        // ========================================
        
        Gate::define('tutor-empresa.ver-inicio', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        Gate::define('tutor-empresa.ver-alumnos-asignados', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        Gate::define('tutor-empresa.ver-detalle-alumno', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        Gate::define('tutor-empresa.gestionar-competencias', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        Gate::define('tutor-empresa.gestionar-calificacion', function (User $user) {
            return $user->role === 'tutor_empresa';
        });

        // ========================================
        // GATES PARA ADMINISTRADOR
        // ========================================
        
        Gate::define('admin.ver-inicio', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.gestionar-ciclos', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.gestionar-competencias', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.gestionar-empresas', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.gestionar-alumnos', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.gestionar-matriz-competencias', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.agregar-recursos', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.importar-datos', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('admin.ver-detalles', function (User $user) {
            return $user->role === 'admin';
        });

        // ========================================
        // GATES COMPARTIDOS (múltiples roles)
        // ========================================
        
        Gate::define('ver-competencias', function (User $user) {
            return in_array($user->role, ['tutor_egibide', 'tutor_empresa', 'admin']);
        });

        Gate::define('ver-seguimientos', function (User $user) {
            return in_array($user->role, ['alumno', 'tutor_egibide']);
        });

        Gate::define('gestionar-notas', function (User $user) {
            return in_array($user->role, ['tutor_egibide', 'tutor_empresa']);
        });

        Gate::define('ver-entregas', function (User $user) {
            return in_array($user->role, ['alumno', 'tutor_egibide']);
        });

        Gate::define('ver-empresas', function (User $user) {
            return in_array($user->role, ['alumno', 'tutor_egibide', 'admin']);
        });

        Gate::define('ver-alumnos', function (User $user) {
            return in_array($user->role, ['tutor_egibide', 'tutor_empresa', 'admin']);
        });

        Gate::define('gestionar-estancias', function (User $user) {
            return in_array($user->role, ['tutor_egibide', 'admin']);
        });

        Gate::define('gestionar-horarios', function (User $user) {
            return in_array($user->role, ['tutor_egibide', 'admin']);
        });
    }
}