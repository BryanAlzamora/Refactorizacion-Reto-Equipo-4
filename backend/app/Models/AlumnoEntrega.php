<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\NotaCuaderno;

class AlumnoEntrega extends Model
{
    protected $table = 'alumno_entrega';

    protected $fillable = [
        'url_entrega',
        'fecha_entrega',
        'alumno_id',
        'entrega_id',
        'observaciones',
        'feedback',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumnos::class);
    }

    public function entrega(): BelongsTo
    {
        return $this->belongsTo(EntregaCuaderno::class, 'entrega_id');
    }
}
