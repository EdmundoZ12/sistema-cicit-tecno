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
        Schema::create('VISITA_PAGINA', function (Blueprint $table) {
            $table->id();
            $table->string('pagina', 255);
            $table->string('ip_address', 45)->nullable(); // Cambiado: soporta IPv4 e IPv6
            $table->foreignId('usuario_id')->nullable()->constrained('USUARIO')->onDelete('set null');
            $table->timestamp('fecha_visita')->useCurrent();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('pagina');
            $table->index('fecha_visita');
            $table->index('session_id');
            $table->index('usuario_id');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('VISITA_PAGINA');
    }
};
