<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ciclos extends Model {
    use HasFactory;
    protected $table = 'ciclos';

    protected $fillable = [
        'nombre',
        'familia_profesional_id',
        'grupo'  // Este es el identificador único del grupo/ciclo
    ];

    /**
     * Get the familia profesional that owns this ciclo
     */
    public function familiaProfesional(): BelongsTo {
        return $this->belongsTo(FamiliaProfesional::class);
    }

    /**
     * Get all cursos for this ciclo
     * NOTA: Verifica si realmente necesitas esta relación
     */
    public function cursos(): HasMany {
        return $this->hasMany(Curso::class, 'ciclo_id');
    }

    /**
     * Get all asignaturas for this ciclo
     */
    public function asignaturas(): HasMany {
        return $this->hasMany(Asignatura::class, 'ciclo_id');
    }

    /**
     * Get all competencias tec for this ciclo
     */
    public function competenciasTec(): HasMany {
        return $this->hasMany(CompetenciaTec::class);
    }

    /**
     * Tutores asignados a este ciclo
     */
    public function tutores(): BelongsToMany {
        return $this->belongsToMany(
            TutorEgibide::class,
            'ciclo_tutor',
            'ciclo_id',
            'tutor_id'
        )->withTimestamps();
    }

    /**
     * Alumnos de este ciclo/grupo
     * La relación se hace a través del campo 'grupo' que es único
     */
    public function alumnos(): HasMany {
        return $this->hasMany(Alumnos::class, 'grupo', 'grupo');
    }
}
