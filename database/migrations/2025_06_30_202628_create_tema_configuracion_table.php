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
        Schema::create('TEMA_CONFIGURACION', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('color_primario', 7); // #HEX format
            $table->string('color_secundario', 7);
            $table->string('color_fondo', 7);
            $table->string('color_texto', 7);
            $table->integer('tamano_fuente_base')->default(16);
            $table->boolean('alto_contraste')->default(false);
            $table->enum('target_edad', ['ninos', 'jovenes', 'adultos'])->nullable();
            $table->boolean('modo_oscuro')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('target_edad');
            $table->index('activo');
            $table->index('modo_oscuro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TEMA_CONFIGURACION');
    }
};
