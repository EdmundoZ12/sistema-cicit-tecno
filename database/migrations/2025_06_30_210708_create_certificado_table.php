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
        Schema::create('CERTIFICADO', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('INSCRIPCION')->onDelete('restrict');
            $table->enum('tipo', ['PARTICIPACION', 'APROBACION', 'MENCION_HONOR']);
            $table->string('codigo_verificacion', 100)->unique();
            $table->date('fecha_emision')->default(now());
            $table->string('url_pdf', 500)->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('inscripcion_id');
            $table->index('tipo');
            $table->index('codigo_verificacion');
            $table->index('fecha_emision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CERTIFICADO');
    }
};
