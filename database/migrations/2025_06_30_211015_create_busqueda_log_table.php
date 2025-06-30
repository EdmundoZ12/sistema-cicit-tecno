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
        Schema::create('BUSQUEDA_LOG', function (Blueprint $table) {
            $table->id();
            $table->string('termino', 255);
            $table->foreignId('usuario_id')->nullable()->constrained('USUARIO')->onDelete('set null');
            $table->integer('resultados')->default(0);
            $table->timestamp('fecha_busqueda')->useCurrent();
            $table->string('ip_address', 45)->nullable(); // Cambiado: soporta IPv4 e IPv6
            $table->timestamps();

            // Índices para optimización
            $table->index('termino');
            $table->index('fecha_busqueda');
            $table->index('usuario_id');
            $table->index(['termino', 'fecha_busqueda']); // Para analytics de búsquedas populares
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BUSQUEDA_LOG');
    }
};
