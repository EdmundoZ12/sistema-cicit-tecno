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
        Schema::create('MENU_ITEM', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 100);
            $table->string('ruta', 255)->nullable();
            $table->string('icono', 50)->nullable();
            $table->integer('orden');
            $table->foreignId('padre_id')->nullable()->constrained('MENU_ITEM')->onDelete('cascade');
            $table->enum('rol', ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR', 'TODOS'])->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para optimización
            $table->index('orden');
            $table->index('rol');
            $table->index('activo');
            $table->index('padre_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MENU_ITEM');
    }
};
