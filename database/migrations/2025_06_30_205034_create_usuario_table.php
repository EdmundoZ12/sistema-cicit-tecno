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
        Schema::create('USUARIO', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('carnet', 20);
            $table->string('email', 255)->unique();
            $table->string('telefono', 20)->nullable();
            $table->string('password', 255);
            $table->enum('rol', ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR']);
            $table->boolean('activo')->default(true);
            $table->string('registro', 20)->unique(); // Campo para login
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();

            // Índices para optimización
            $table->index('rol');
            $table->index('activo');
            $table->index('registro'); // Para login rápido
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('USUARIO');
    }
};
