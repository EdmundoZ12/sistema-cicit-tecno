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
        Schema::create('CONFIGURACION_USUARIO', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('USUARIO')->onDelete('cascade');
            $table->foreignId('tema_id')->nullable()->constrained('TEMA_CONFIGURACION')->onDelete('set null');
            $table->integer('tamano_fuente')->default(16);
            $table->boolean('alto_contraste')->default(false);
            $table->boolean('modo_automatico')->default(true); // Cambio día/noche automático
            $table->timestamps();

            // Índices para optimización
            $table->index('usuario_id');
            $table->index('tema_id');
            $table->index('modo_automatico');

            // Constraint único: un usuario solo puede tener una configuración
            $table->unique('usuario_id', 'uk_config_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CONFIGURACION_USUARIO');
    }
};
