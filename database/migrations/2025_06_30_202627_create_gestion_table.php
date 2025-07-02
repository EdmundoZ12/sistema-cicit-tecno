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
        Schema::create('GESTION', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Eliminada restricción unique()
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('activo');
        });

        // Agregar constraint después de crear la tabla (usando SQL crudo)
        DB::statement('ALTER TABLE "GESTION" ADD CONSTRAINT chk_gestion_fechas CHECK (fecha_fin > fecha_inicio)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('GESTION');
    }
};
