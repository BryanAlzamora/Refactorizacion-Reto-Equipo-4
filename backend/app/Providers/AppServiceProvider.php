<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. SUPER ADMIN (Pase maestro)
        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        // 2. Roles Puros
        Gate::define('is-alumno', fn(User $user) => $user->role === 'alumno');
        Gate::define('is-tutor-egibide', fn(User $user) => $user->role === 'tutor_egibide');
        Gate::define('is-tutor-empresa', fn(User $user) => $user->role === 'tutor_empresa');
        Gate::define('is-admin', fn(User $user) => $user->role === 'admin');

        // 3. Gates Compartidos (CORREGIDO PARA TU ERROR ACTUAL)
Gate::define('gestionar-competencias', function ($user) {
            return in_array($user->role, ['admin', 'tutor_egibide', 'tutor_empresa']);
        });
        // AQUÍ ESTÁ EL CAMBIO PRINCIPAL: Añadido 'alumno'
        Gate::define('ver-alumnos', fn(User $user) => in_array($user->role, ['admin', 'tutor_egibide', 'tutor_empresa', 'alumno']));

        // Permisos necesarios para ver notas y competencias
        Gate::define('ver-competencias', fn(User $user) => in_array($user->role, ['admin', 'tutor_egibide', 'tutor_empresa', 'alumno']));
        Gate::define('gestionar-notas', fn(User $user) => in_array($user->role, ['tutor_egibide', 'tutor_empresa', 'alumno']));

        // Resto de permisos
        Gate::define('ver-empresas', fn(User $user) => in_array($user->role, ['admin', 'tutor_egibide', 'alumno']));
        Gate::define('ver-seguimientos', fn(User $user) => in_array($user->role, ['alumno', 'tutor_egibide']));
        Gate::define('gestionar-estancias', fn(User $user) => in_array($user->role, ['tutor_egibide', 'admin']));
        Gate::define('gestionar-horarios', fn(User $user) => in_array($user->role, ['tutor_egibide', 'admin']));
    }
}