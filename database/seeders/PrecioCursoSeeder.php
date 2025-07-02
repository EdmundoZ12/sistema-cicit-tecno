<?php

namespace Database\Seeders;

use App\Models\PrecioCurso;
use App\Models\Curso;
use App\Models\TipoParticipante;
use Illuminate\Database\Seeder;

class PrecioCursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener cursos y tipos de participante
        $cursos = Curso::all();
        $tiposParticipante = TipoParticipante::all();

        // Definir precios base por nivel y duración
        $preciosBase = [
            'BASICO' => [
                'EST_FICCT' => 120.00,
                'EST_UAGRM' => 180.00,
                'PARTICULAR' => 250.00,
            ],
            'INTERMEDIO' => [
                'EST_FICCT' => 150.00,
                'EST_UAGRM' => 220.00,
                'PARTICULAR' => 300.00,
            ],
            'AVANZADO' => [
                'EST_FICCT' => 200.00,
                'EST_UAGRM' => 280.00,
                'PARTICULAR' => 400.00,
            ],
        ];

        foreach ($cursos as $curso) {
            foreach ($tiposParticipante as $tipo) {
                $nivel = $curso->nivel ?: 'BASICO';
                
                if (isset($preciosBase[$nivel][$tipo->codigo])) {
                    $precioBase = $preciosBase[$nivel][$tipo->codigo];
                    
                    // Ajustar precio según duración
                    $factor = 1.0;
                    if ($curso->duracion_horas <= 30) {
                        $factor = 0.8;  // Cursos cortos
                    } elseif ($curso->duracion_horas >= 50) {
                        $factor = 1.3;  // Cursos largos
                    }
                    
                    $precioFinal = round($precioBase * $factor, 2);
                    
                    // Aplicar descuento directamente al precio final
                    $descuento = $this->getDescuentoEspecial($tipo->codigo, $curso);
                    if ($descuento > 0) {
                        $precioFinal = round($precioFinal * (1 - $descuento / 100), 2);
                    }
                    
                    PrecioCurso::create([
                        'curso_id' => $curso->id,
                        'tipo_participante_id' => $tipo->id,
                        'precio' => $precioFinal,
                        'activo' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Obtener descuento especial según tipo y curso
     */
    private function getDescuentoEspecial(string $tipoCode, $curso): float
    {
        // Descuentos especiales
        if ($tipoCode === 'EST_FICCT') {
            return 10.0; // 10% descuento para estudiantes FICCT
        }
        
        if ($tipoCode === 'EST_UAGRM' && $curso->nivel === 'BASICO') {
            return 5.0; // 5% descuento para estudiantes UAGRM en cursos básicos
        }
        
        return 0.0; // Sin descuento
    }
}
