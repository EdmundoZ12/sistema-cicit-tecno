<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('PAGO', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preinscripcion_id')->constrained('PREINSCRIPCION')->onDelete('restrict');
            $table->timestamp('fecha_pago')->useCurrent();
            $table->decimal('monto', 10, 2);
            $table->string('recibo', 50)->unique();
            $table->timestamps();

            // Índices para optimización
            $table->index('preinscripcion_id');
            $table->index('fecha_pago');
            $table->index('recibo');

            // Constraint único: una preinscripción solo puede tener un pago
            $table->unique('preinscripcion_id', 'uk_pago_preinscripcion');
        });

        // Agregar constraint para monto positivo
        DB::statement('ALTER TABLE "PAGO" ADD CONSTRAINT chk_monto_positivo CHECK (monto > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PAGO');
    }
};
