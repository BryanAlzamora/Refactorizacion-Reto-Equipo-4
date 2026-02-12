<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TutorEgibide extends Model {
    use HasFactory;
    protected $table = 'tutores';

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'ciudad',
        'user_id',
        'alias'
    ];

    /**
     * Usuario asociado al tutor
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * Ciclos a los que pertenece este tutor
     */
    public function ciclos(): BelongsToMany {
        return $this->belongsToMany(
            Ciclos::class,
            'ciclo_tutor',
            'tutor_id',
            'ciclo_id'
        )->withTimestamps();
    }

    /**
     * Asignaturas que imparte este tutor
     */
    public function asignaturas(): BelongsToMany {
        return $this->belongsToMany(
            Asignatura::class,
            'tutor_asignatura',
            'tutor_id',
            'asignatura_id'
        )->withTimestamps();
    }

    /**
     * Alumnos asignados directamente a este tutor
     */
    public function alumnos(): HasMany {
        return $this->hasMany(Alumnos::class, 'tutor_id', 'id');
    }

    /**
     * Alumnos con datos de la estancia (pivot)
     */
    public function alumnosConEstancia(): BelongsToMany {
        return $this->belongsToMany(
            Alumnos::class,
            'estancias',
            'tutor_id',
            'alumno_id'
        )->withPivot([
            'id',
            'puesto',
            'fecha_inicio',
            'fecha_fin',
            'horas_totales',
            'instructor_id',
            'empresa_id',
            'curso_id'
        ])->withTimestamps();
    }

    /**
     * Familias profesionales a las que pertenece este tutor
     */
    public function familias(): BelongsToMany {
        return $this->belongsToMany(
            FamiliaProfesional::class,
            'familia_tutor',
            'tutor_id',
            'familias_profesionales_id'
        )->withTimestamps();
    }

    /**
     * Cursos relacionados (si aplica)
     * NOTA: Verifica si realmente necesitas esta relación o si deberías usar 'ciclos'
     */
    public function cursos(): BelongsToMany {
        return $this->belongsToMany(
            Curso::class,
            'curso_tutor',
            'tutor_id',
            'curso_id'
        )->withTimestamps();
    }
}
