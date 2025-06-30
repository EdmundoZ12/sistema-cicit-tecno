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
        Schema::create('TAREA', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('CURSO')->onDelete('cascade');
            $table->string('titulo', 100);
            $table->text('descripcion')->nullable();
            $table->date('fecha_asignacion');
            $table->timestamps();

            // Índices para optimización
            $table->index('curso_id');
            $table->index('fecha_asignacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TAREA');
    }
};
