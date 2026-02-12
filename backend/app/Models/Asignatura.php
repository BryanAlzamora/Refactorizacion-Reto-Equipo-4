<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    use HasFactory;
    protected $table = 'asignaturas';

    protected $fillable = [
        'codigo_asignatura',
        'nombre_asignatura',
        'ciclo_id'
    ];

    /**
     * Obtener el ciclo al que pertenece la asignatura
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclos::class, 'ciclo_id');
    }

    /**
     * Obtener los tutores que imparten esta asignatura
     */
    public function tutores(): BelongsToMany
    {
        return $this->belongsToMany(
            TutorEgibide::class,
            'tutor_asignatura',
            'asignatura_id',
            'tutor_id'
        )->withTimestamps();
    }

    /**
     * Obtener las notas de los alumnos en esta asignatura
     */
    public function notasAsignatura()
    {
        return $this->hasMany(NotaAsignatura::class, 'asignatura_id');
    }

    public function resultadosAprendizaje(): HasMany
    {
        return $this->hasMany(
            ResultadoAprendizaje::class,
            'asignatura_id',
            'id'
        );
    }
}
