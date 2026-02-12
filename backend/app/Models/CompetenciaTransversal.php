<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetenciaTransversal extends Model {
  use HasFactory;
  protected $table = 'competencias_trans';

  protected $fillable = [
    'descripcion',
    'nivel',
    'familia_profesional_id',
  ];

  /**
   * Get the familia profesional that owns this competencia trans
   */
  public function familiaProfesional(): BelongsTo {
    return $this->belongsTo(FamiliaProfesional::class);
  }

  /**
   * Get all notas competencia trans for this competencia
   */
  public function notasCompetenciaTrans(): HasMany {
    return $this->hasMany(NotaCompetenciaTransversal::class);
  }
}
