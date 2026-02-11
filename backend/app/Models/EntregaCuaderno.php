<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntregaCuaderno extends Model {
  protected $table = 'entrega_cuaderno';

  protected $fillable = [
    'fecha_creacion',
    'fecha_limite',
    'tutor_id',
    'descripcion',
  ];

  protected $casts = [
    'fecha_creacion' => 'date',
    'fecha_limite' => 'date',
  ];

  public function tutor(): BelongsTo {
    return $this->belongsTo(TutorEgibide::class, 'tutor_id');
  }

  public function entregas(): HasMany {
    return $this->hasMany(AlumnoEntrega::class, 'entrega_id');
  }
}
