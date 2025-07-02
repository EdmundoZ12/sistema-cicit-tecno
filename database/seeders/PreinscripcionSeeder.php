<?php

namespace Database\Seeders;

use App\Models\Preinscripcion;
use App\Models\Curso;
use App\Models\Participante;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PreinscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener cursos y participantes
        $cursos = Curso::all();
        $participantes = Participante::all();

        $preinscripciones = [];

        // Generar preinscripciones variadas
        foreach ($cursos as $curso) {
            // Número aleatorio de preinscripciones por curso (entre 3 y 8)
            $numPreinscripciones = rand(3, min(8, $participantes->count()));
            $participantesSeleccionados = $participantes->random($numPreinscripciones);
            
            foreach ($participantesSeleccionados as $index => $participante) {
                // Fecha de preinscripción realista (antes del inicio del curso)
                $fechaPreinscripcion = Carbon::parse($curso->fecha_inicio)->subDays(rand(7, 30));
                
                // Estado basado en el número de cupos
                $estado = $this->determinarEstado($curso, $index, $numPreinscripciones);
                
                $preinscripciones[] = [
                    'curso_id' => $curso->id,
                    'participante_id' => $participante->id,
                    'fecha_preinscripcion' => $fechaPreinscripcion,
                    'estado' => $estado,
                    'observaciones' => $this->generarObservaciones($estado),
                ];
            }
        }

        foreach ($preinscripciones as $preinscripcion) {
            Preinscripcion::create($preinscripcion);
        }
    }

    /**
     * Determinar estado realista de la preinscripción
     */
    private function determinarEstado($curso, $index, $total): string
    {
        // Para cursos con cupos llenos, la mayoría fueron aprobadas
        if ($curso->cupos_ocupados >= $curso->cupos_totales) {
            return $index < ($total * 0.8) ? 'APROBADA' : 'RECHAZADA';
        }
        
        // Para cursos con inicio próximo, algunos ya fueron procesados
        $fechaInicio = Carbon::parse($curso->fecha_inicio);
        if ($fechaInicio->isPast() || $fechaInicio->diffInDays(now()) < 30) {
            if ($index < ($total * 0.7)) {
                return 'APROBADA';
            } elseif ($index < ($total * 0.9)) {
                return 'RECHAZADA';
            } else {
                return 'PENDIENTE';
            }
        }
        
        // Para cursos futuros, mix de estados
        if ($index < ($total * 0.6)) {
            return 'APROBADA';
        } elseif ($index < ($total * 0.8)) {
            return 'PENDIENTE';
        } else {
            return 'RECHAZADA';
        }
    }

    /**
     * Generar observaciones simples
     */
    private function generarObservaciones($estado): ?string
    {
        if ($estado === 'PENDIENTE') {
            return null;
        }

        if ($estado === 'APROBADA') {
            $observaciones = [
                'Cumple con los requisitos. Bienvenido al curso.',
                'Perfil adecuado para el curso.',
                'Aprobado. Se enviará información adicional por email.',
                'Bienvenido. Favor confirmar asistencia.',
            ];
        } else {
            $observaciones = [
                'No cumple con los requisitos mínimos.',
                'Cupo completo. Se sugiere postular al siguiente período.',
                'Experiencia insuficiente para el nivel del curso.',
                'Documentación incompleta.',
            ];
        }

        return $observaciones[array_rand($observaciones)];
    }
}
