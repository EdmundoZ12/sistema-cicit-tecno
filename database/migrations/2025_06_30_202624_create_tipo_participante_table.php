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
        Schema::create('TIPO_PARTICIPANTE', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('descripcion', 255);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('codigo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TIPO_PARTICIPANTE');
    }
};
