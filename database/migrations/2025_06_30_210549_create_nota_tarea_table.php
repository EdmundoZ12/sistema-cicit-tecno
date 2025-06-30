<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('NOTA_TAREA', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('TAREA')->onDelete('cascade');
            $table->foreignId('inscripcion_id')->constrained('INSCRIPCION')->onDelete('cascade');
            $table->decimal('nota', 5, 2);
            $table->timestamps();

            // Índices para optimización
            $table->index('tarea_id');
            $table->index('inscripcion_id');

            // Constraint único: una inscripción solo puede tener una nota por tarea
            $table->unique(['tarea_id', 'inscripcion_id'], 'uk_tarea_inscripcion');
        });

        // Agregar constraint para nota válida (0-100)
        DB::statement('ALTER TABLE "NOTA_TAREA" ADD CONSTRAINT chk_nota_valida CHECK (nota >= 0 AND nota <= 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('NOTA_TAREA');
    }
};
