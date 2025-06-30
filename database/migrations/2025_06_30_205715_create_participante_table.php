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
        Schema::create('PARTICIPANTE', function (Blueprint $table) {
            $table->id();
            $table->string('carnet', 20);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('universidad', 255)->nullable();
            $table->foreignId('tipo_participante_id')->constrained('TIPO_PARTICIPANTE')->onDelete('restrict');
            $table->boolean('activo')->default(true);
            $table->string('registro', 20)->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('tipo_participante_id');
            $table->index('email');
            $table->index('carnet');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PARTICIPANTE');
    }
};
