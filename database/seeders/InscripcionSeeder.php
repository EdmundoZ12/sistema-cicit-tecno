<?php

namespace Database\Seeders;

use App\Models\Inscripcion;
use App\Models\Preinscripcion;
use App\Models\PrecioCurso;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener solo las preinscripciones aprobadas
        $preinscripcionesAprobadas = Preinscripcion::where('estado', 'APROBADA')->get();

        foreach ($preinscripcionesAprobadas as $preinscripcion) {
            // Verificar si el curso aún tiene cupos disponibles
            $curso = $preinscripcion->curso;
            if ($curso->cupos_ocupados >= $curso->cupos_totales) {
                continue; // Skip si no hay cupos
            }

            // No todos los pre-inscritos se inscriben realmente (70% aproximadamente)
            if (rand(1, 100) <= 70) {
                // Fecha de inscripción (después de la preinscripción)
                $fechaInscripcion = Carbon::parse($preinscripcion->fecha_preinscripcion)->addDays(rand(1, 7));

                // Estado de la inscripción basado en el estado del curso
                $estado = $this->determinarEstadoInscripcion($preinscripcion->curso);

                // Nota final si ya completó
                $notaFinal = null;
                if ($estado === 'APROBADO') {
                    $notaFinal = rand(70, 100); // Nota aprobatoria
                } elseif ($estado === 'REPROBADO') {
                    $notaFinal = rand(30, 69); // Nota reprobatoria
                }

                $inscripcion = [
                    'preinscripcion_id' => $preinscripcion->id,
                    'participante_id' => $preinscripcion->participante_id,
                    'curso_id' => $preinscripcion->curso_id,
                    'fecha_inscripcion' => $fechaInscripcion,
                    'estado' => $estado,
                    'nota_final' => $notaFinal,
                    'observaciones' => $this->generarObservaciones($estado),
                ];

                Inscripcion::create($inscripcion);
            }
        }
    }

    /**
     * Determinar estado de inscripción basado en el curso
     */
    private function determinarEstadoInscripcion($curso): string
    {
        $fechaInicio = \Carbon\Carbon::parse($curso->fecha_inicio);
        $fechaFin = \Carbon\Carbon::parse($curso->fecha_fin);
        
        if ($fechaFin->isPast()) {
            // Para cursos finalizados, la mayoría aprobaron
            return rand(1, 100) <= 75 ? 'APROBADO' : 'REPROBADO';
        } elseif ($fechaInicio->isPast() && $fechaFin->isFuture()) {
            // Para cursos en curso, están inscritos
            return 'INSCRITO';
        } else {
            // Para cursos programados, están inscritos
            return 'INSCRITO';
        }
    }

    /**
     * Generar observaciones
     */
    private function generarObservaciones($estado): ?string
    {
        $observaciones = [];

        switch ($estado) {
            case 'APROBADO':
                $observaciones = [
                    'Curso completado satisfactoriamente',
                    'Excelente participación durante el curso',
                    'Cumplió con todos los requisitos',
                    'Buen rendimiento en evaluaciones',
                    null
                ];
                break;
                
            case 'REPROBADO':
                $observaciones = [
                    'No alcanzó la nota mínima requerida',
                    'Faltó a varias clases importantes',
                    'No entregó trabajos requeridos',
                    'Necesita reforzar conocimientos básicos'
                ];
                break;
                
            case 'INSCRITO':
                $observaciones = [
                    'Inscripción activa',
                    'Documentación completa',
                    null,
                    null,
                    null
                ];
                break;
                
            case 'RETIRADO':
                $observaciones = [
                    'Se retiró por motivos personales',
                    'Incompatibilidad de horarios',
                    'Motivos laborales',
                    'Cambio de residencia'
                ];
                break;
                
            default:
                $observaciones = [null, null, null];
        }

        return $observaciones[array_rand($observaciones)];
    }
}
