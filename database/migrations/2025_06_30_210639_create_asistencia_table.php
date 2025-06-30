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
        Schema::create('ASISTENCIA', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('INSCRIPCION')->onDelete('cascade');
            $table->date('fecha');
            $table->enum('estado', ['PRESENTE', 'AUSENTE', 'JUSTIFICADO']);
            $table->timestamps();

            // Índices para optimización
            $table->index('inscripcion_id');
            $table->index('fecha');
            $table->index('estado');

            // Constraint único: una inscripción solo puede tener un registro de asistencia por fecha
            $table->unique(['inscripcion_id', 'fecha'], 'uk_asistencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ASISTENCIA');
    }
};
