<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaCuaderno extends Model {
  protected $table = 'notas_cuaderno';

  protected $fillable = [
    'nota',
    'cuaderno_practicas_id',
  ];

  protected $casts = [
    'nota' => 'decimal:2',
  ];

  /**
   * Get the cuaderno practicas that owns this nota
   */
  public function alumno(): BelongsTo {
    return $this->belongsTo(Alumnos::class);
  }
}
