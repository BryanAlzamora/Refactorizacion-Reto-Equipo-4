<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('competencias_tec_ra', function (Blueprint $table) {
            $table->foreignId('competencia_tec_id')
                ->constrained('competencias_tec')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('resultado_aprendizaje_id')
                ->constrained('resultados_aprendizaje')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->primary(['competencia_tec_id', 'resultado_aprendizaje_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('competencias_tec_ra');
    }
};
