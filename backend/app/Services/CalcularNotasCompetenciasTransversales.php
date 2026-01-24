<?php

namespace App\Services;

use App\Models\Estancia;
use App\Models\NotaCompetenciaTransversal;

class CalcularNotasCompetenciasTransversales {

  public function calcularNotasTransversales($alumno_id) {
    $estancia = Estancia::where('alumno_id', $alumno_id)->firstOrFail();
    $notasTransversales = NotaCompetenciaTransversal::where('estancia_id', $estancia->id)->get();

    if ($notasTransversales->isEmpty()) {
      return [
        'estancia_id' => $estancia->id,
        'nota_media' => null
      ];
    }

    $suma = 0;

    foreach ($notasTransversales as $nota) {
      $suma += $nota->nota;
    }

    $media = $suma / $notasTransversales->count();

    return [
      'estancia_id' => $estancia->id,
      'nota_media' => round($this->convertirAEscala10($media), 2)
    ];
  }

  private function convertirAEscala10($nota) {
    return round($nota * 2.5, 2);
  }
}
