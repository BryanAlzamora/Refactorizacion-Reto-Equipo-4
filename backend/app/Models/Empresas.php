<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TutorEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Empresas extends Model {
    use HasFactory;
    protected $fillable = [
        'nombre',
        'cif',
        'telefono',
        'email',
        'direccion'
    ];

    /**
     * Get all instructores for this empresa
     */


    public function instructores()
    {
        return $this->belongsToMany(
            TutorEmpresa::class,
            'estancias',
            'empresa_id',
            'instructor_id'
        )->distinct();
    }

    /**
     * Get all estancias for this empresa
     */
    public function estancias(): HasMany {
        return $this->hasMany(Estancia::class,'empresa_id', 'id');
    }
}
