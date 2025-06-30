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
        Schema::create('CURSO', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->integer('duracion_horas');
            $table->string('nivel', 50)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->foreignId('tutor_id')->constrained('USUARIO')->onDelete('restrict');
            $table->foreignId('gestion_id')->constrained('GESTION')->onDelete('restrict');
            $table->string('aula', 50)->nullable();
            $table->integer('cupos_totales');
            $table->integer('cupos_ocupados')->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('tutor_id');
            $table->index('gestion_id');
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('activo');
            $table->index(['cupos_totales', 'cupos_ocupados']);
        });

        // Agregar constraints con SQL crudo
        DB::statement('ALTER TABLE "CURSO" ADD CONSTRAINT chk_curso_duracion CHECK (duracion_horas > 0)');
        DB::statement('ALTER TABLE "CURSO" ADD CONSTRAINT chk_curso_cupos_totales CHECK (cupos_totales > 0)');
        DB::statement('ALTER TABLE "CURSO" ADD CONSTRAINT chk_curso_cupos_ocupados CHECK (cupos_ocupados >= 0)');
        DB::statement('ALTER TABLE "CURSO" ADD CONSTRAINT chk_curso_cupos CHECK (cupos_ocupados <= cupos_totales)');
        DB::statement('ALTER TABLE "CURSO" ADD CONSTRAINT chk_curso_fechas CHECK (fecha_fin > fecha_inicio)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CURSO');
    }
};
