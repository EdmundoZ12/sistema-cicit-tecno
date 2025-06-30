<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('PREINSCRIPCION', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participante_id')->constrained('PARTICIPANTE')->onDelete('restrict');
            $table->foreignId('curso_id')->constrained('CURSO')->onDelete('restrict');
            $table->timestamp('fecha_preinscripcion')->useCurrent();
            $table->enum('estado', ['PENDIENTE', 'APROBADA', 'RECHAZADA'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('participante_id');
            $table->index('curso_id');
            $table->index('estado');
            $table->index('fecha_preinscripcion');

            // Constraint único: un participante no puede preinscribirse dos veces al mismo curso
            $table->unique(['participante_id', 'curso_id'], 'uk_preinscripcion_participante_curso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PREINSCRIPCION');
    }
};
