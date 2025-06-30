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
        Schema::create('CONFIGURACION_SITIO', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->string('descripcion', 255)->nullable();
            $table->enum('tipo', ['string', 'number', 'boolean', 'json'])->default('string');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('clave');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CONFIGURACION_SITIO');
    }
};
