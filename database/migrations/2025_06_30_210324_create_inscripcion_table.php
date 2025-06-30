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
        Schema::create('INSCRIPCION', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participante_id')->constrained('PARTICIPANTE')->onDelete('restrict');
            $table->foreignId('curso_id')->constrained('CURSO')->onDelete('restrict');
            $table->foreignId('preinscripcion_id')->constrained('PREINSCRIPCION')->onDelete('restrict');
            $table->timestamp('fecha_inscripcion')->useCurrent();
            $table->decimal('nota_final', 4, 2)->nullable();
            $table->enum('estado', ['INSCRITO', 'APROBADO', 'REPROBADO', 'RETIRADO'])->default('INSCRITO');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('participante_id');
            $table->index('curso_id');
            $table->index('preinscripcion_id');
            $table->index('estado');
            $table->index('fecha_inscripcion');

            // Constraints únicos
            $table->unique(['participante_id', 'curso_id'], 'uk_inscripcion_participante_curso');
            $table->unique('preinscripcion_id', 'uk_inscripcion_preinscripcion');
        });

        // Agregar constraint para nota válida (0-100)
        DB::statement('ALTER TABLE "INSCRIPCION" ADD CONSTRAINT chk_nota_final CHECK (nota_final >= 0 AND nota_final <= 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('INSCRIPCION');
    }
};
