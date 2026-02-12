<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetenciaTec extends Model {
    use HasFactory;
    protected $table = 'competencias_tec';

    protected $fillable = [
        'ciclo_id',
        'descripcion'
    ];

    /**
     * Get the ciclo that owns this competencia tec
     */
    public function ciclo(): BelongsTo {
        return $this->belongsTo(Ciclos::class);
    }

    /**
     * Get all resultados aprendizaje that have this competencia tec
     */
    public function resultadosAprendizaje(): BelongsToMany {
        return $this->belongsToMany(
            ResultadoAprendizaje::class,
            'competencia_tec_ra',
            'competencia_tec_id',
            'resultado_aprendizaje_id'
        );
    }

    /**
     * Get all notas competencia tec for this competencia
     */
    public function notasCompetenciaTec(): HasMany {
        return $this->hasMany(NotaCompetenciaTec::class);
    }
}
