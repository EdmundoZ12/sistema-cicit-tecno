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
        Schema::create('ESTADISTICA', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50); // 'cursos_activos', 'inscripciones_mes', etc.
            $table->decimal('valor', 15, 2);
            $table->date('fecha')->default(now());
            $table->string('descripcion', 255)->nullable();
            $table->json('metadata')->nullable(); // Para datos adicionales flexibles
            $table->timestamps();

            // Índices para optimización
            $table->index('tipo');
            $table->index('fecha');
            $table->index(['tipo', 'fecha']); // Índice compuesto para consultas por tipo y fecha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ESTADISTICA');
    }
};
