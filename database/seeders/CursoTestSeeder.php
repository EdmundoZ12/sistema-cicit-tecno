<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\PrecioCurso;
use App\Models\Usuario;
use App\Models\Gestion;
use App\Models\TipoParticipante;
use Illuminate\Database\Seeder;

class CursoTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que existen los datos necesarios
        $tutor = Usuario::where('rol', 'TUTOR')->first();
        $gestion = Gestion::where('activo', true)->first();
        $tiposParticipante = TipoParticipante::where('activo', true)->get();

        if (!$tutor || !$gestion || $tiposParticipante->isEmpty()) {
            $this->command->warn('Se necesitan tutores, gestiones y tipos de participante activos para crear cursos de prueba.');
            return;
        }

        // Crear cursos de prueba
        $cursos = [
            [
                'nombre' => 'Curso de Desarrollo Web con Laravel',
                'descripcion' => 'Aprende a desarrollar aplicaciones web modernas utilizando el framework Laravel. Incluye conceptos de MVC, Eloquent ORM, autenticación y más.',
                'duracion_horas' => 40,
                'nivel' => 'INTERMEDIO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Aula Virtual 1',
                'cupos_totales' => 25,
                'cupos_ocupados' => 15,
                'fecha_inicio' => now()->addDays(7),
                'fecha_fin' => now()->addDays(37),
                'activo' => true,
            ],
            [
                'nombre' => 'Introducción a Vue.js',
                'descripcion' => 'Curso básico de Vue.js para principiantes. Aprende los fundamentos del framework JavaScript más popular.',
                'duracion_horas' => 30,
                'nivel' => 'BASICO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Aula 202',
                'cupos_totales' => 20,
                'cupos_ocupados' => 8,
                'fecha_inicio' => now()->addDays(14),
                'fecha_fin' => now()->addDays(44),
                'activo' => true,
            ],
            [
                'nombre' => 'Análisis de Datos con Python',
                'descripcion' => 'Curso avanzado de análisis de datos utilizando Python, pandas, numpy y matplotlib.',
                'duracion_horas' => 50,
                'nivel' => 'AVANZADO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Laboratorio 3',
                'cupos_totales' => 15,
                'cupos_ocupados' => 12,
                'fecha_inicio' => now()->subDays(5),
                'fecha_fin' => now()->addDays(25),
                'activo' => true,
            ],
        ];

        foreach ($cursos as $cursoData) {
            $curso = Curso::create($cursoData);

            // Crear precios para cada tipo de participante
            foreach ($tiposParticipante as $tipo) {
                PrecioCurso::create([
                    'curso_id' => $curso->id,
                    'tipo_participante_id' => $tipo->id,
                    'precio' => match($tipo->codigo) {
                        'EST' => 150.00,
                        'DOC' => 200.00,
                        'EXT' => 300.00,
                        default => 250.00
                    }
                ]);
            }
        }

        $this->command->info('Cursos de prueba creados exitosamente.');
    }
}
