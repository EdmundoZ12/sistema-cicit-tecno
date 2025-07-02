<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Gestion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener gestión actual y tutor
        $gestion = Gestion::where('activo', true)->first();
        $tutor = Usuario::where('rol', 'TUTOR')->first();

        $cursos = [
            [
                'nombre' => 'Programación Web con PHP y Laravel',
                'descripcion' => 'Curso completo de desarrollo web utilizando PHP y el framework Laravel. Incluye bases de datos, autenticación, y desarrollo de APIs.',
                'duracion_horas' => 60,
                'nivel' => 'INTERMEDIO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Laboratorio de Computación 1',
                'cupos_totales' => 25,
                'cupos_ocupados' => 18,
                'fecha_inicio' => '2025-08-01',
                'fecha_fin' => '2025-09-30',
                'activo' => true,
            ],
            [
                'nombre' => 'Desarrollo Frontend con Vue.js',
                'descripcion' => 'Aprende a crear interfaces de usuario modernas y reactivas con Vue.js 3, incluyendo composables, routing y gestión de estado.',
                'duracion_horas' => 40,
                'nivel' => 'INTERMEDIO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Virtual',
                'cupos_totales' => 30,
                'cupos_ocupados' => 12,
                'fecha_inicio' => '2025-08-15',
                'fecha_fin' => '2025-10-15',
                'activo' => true,
            ],
            [
                'nombre' => 'Base de Datos con MySQL',
                'descripcion' => 'Curso fundamental de bases de datos relacionales utilizando MySQL. Desde conceptos básicos hasta optimización avanzada.',
                'duracion_horas' => 32,
                'nivel' => 'BASICO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Laboratorio de Computación 2',
                'cupos_totales' => 20,
                'cupos_ocupados' => 20,
                'fecha_inicio' => '2025-07-15',
                'fecha_fin' => '2025-08-30',
                'activo' => true,
            ],
            [
                'nombre' => 'Python para Data Science',
                'descripcion' => 'Introducción al análisis de datos con Python, utilizando pandas, numpy, matplotlib y scikit-learn.',
                'duracion_horas' => 48,
                'nivel' => 'INTERMEDIO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Laboratorio de Computación 3',
                'cupos_totales' => 15,
                'cupos_ocupados' => 8,
                'fecha_inicio' => '2025-09-01',
                'fecha_fin' => '2025-11-15',
                'activo' => true,
            ],
            [
                'nombre' => 'Diseño UX/UI para Aplicaciones Web',
                'descripcion' => 'Aprende los principios de experiencia de usuario y diseño de interfaces para crear aplicaciones web atractivas y funcionales.',
                'duracion_horas' => 30,
                'nivel' => 'BASICO',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'aula' => 'Aula de Diseño',
                'cupos_totales' => 18,
                'cupos_ocupados' => 15,
                'fecha_inicio' => '2025-07-01',
                'fecha_fin' => '2025-07-31',
                'activo' => true,
            ]
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }
    }
}
