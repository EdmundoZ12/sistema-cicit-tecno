<?php

namespace Database\Seeders;

use App\Models\Estadistica;
use Illuminate\Database\Seeder;

class EstadisticaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Datos basados exactamente en el script SQL de CICIT
     */
    public function run(): void
    {
        $estadisticas = [
            [
                'tipo' => 'total_usuarios',
                'valor' => 3,
                'fecha' => now(),
                'descripcion' => 'Total de usuarios del sistema registrados',
                'metadata' => json_encode(['categoria' => 'usuarios', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'total_participantes',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Total de participantes inscritos',
                'metadata' => json_encode(['categoria' => 'participantes', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'total_cursos',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Total de cursos de certificación creados',
                'metadata' => json_encode(['categoria' => 'cursos', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'cursos_activos',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Cursos actualmente en desarrollo',
                'metadata' => json_encode(['categoria' => 'cursos', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'total_inscripciones',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Total de inscripciones procesadas',
                'metadata' => json_encode(['categoria' => 'inscripciones', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'inscripciones_pendientes',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Preinscripciones pendientes de aprobación',
                'metadata' => json_encode(['categoria' => 'inscripciones', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'certificados_emitidos',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Total de certificados emitidos',
                'metadata' => json_encode(['categoria' => 'certificados', 'unidad' => 'cantidad'])
            ],
            [
                'tipo' => 'ingresos_totales',
                'valor' => 0.00,
                'fecha' => now(),
                'descripcion' => 'Ingresos totales por cursos de certificación',
                'metadata' => json_encode(['categoria' => 'finanzas', 'unidad' => 'bolivianos'])
            ],
            [
                'tipo' => 'participantes_ficct',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Estudiantes FICCT certificados',
                'metadata' => json_encode(['categoria' => 'participantes', 'unidad' => 'cantidad', 'tipo_participante' => 'EST_FICCT'])
            ],
            [
                'tipo' => 'participantes_uagrm',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Estudiantes UAGRM certificados',
                'metadata' => json_encode(['categoria' => 'participantes', 'unidad' => 'cantidad', 'tipo_participante' => 'EST_UAGRM'])
            ],
            [
                'tipo' => 'participantes_externos',
                'valor' => 0,
                'fecha' => now(),
                'descripcion' => 'Participantes externos certificados',
                'metadata' => json_encode(['categoria' => 'participantes', 'unidad' => 'cantidad', 'tipo_participante' => 'PARTICULAR'])
            ]
        ];

        foreach ($estadisticas as $estadistica) {
            Estadistica::create($estadistica);
        }
    }
}
