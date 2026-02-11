<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('alumno_entrega', function (Blueprint $table) {
            $table->id();
            $table->string('url_entrega', 255);
            $table->date('fecha_entrega');
            $table->foreignId('alumno_id')
                ->constrained('alumnos')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('entrega_id')
                ->constrained('entrega_cuaderno')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('observaciones')->nullable();
            $table->enum('feedback',['Bien', 'Regular', 'Debe mejorar'])->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('cuadernos_practicas');
    }
};
