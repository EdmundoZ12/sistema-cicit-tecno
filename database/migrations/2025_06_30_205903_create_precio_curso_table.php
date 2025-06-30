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
        Schema::create('PRECIO_CURSO', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('CURSO')->onDelete('cascade');
            $table->foreignId('tipo_participante_id')->constrained('TIPO_PARTICIPANTE')->onDelete('restrict');
            $table->decimal('precio', 10, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('curso_id');
            $table->index('tipo_participante_id');
            $table->index('activo');

            // Constraint único: un curso solo puede tener un precio por tipo de participante
            $table->unique(['curso_id', 'tipo_participante_id'], 'uk_precio_curso_tipo');
        });

        // Agregar constraint para precio positivo
        DB::statement('ALTER TABLE "PRECIO_CURSO" ADD CONSTRAINT chk_precio_positivo CHECK (precio > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PRECIO_CURSO');
    }
};
