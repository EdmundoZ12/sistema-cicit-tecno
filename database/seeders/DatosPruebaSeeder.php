<?php

namespace Database\Seeders;

use Illu        // Crear curso de prueba
        $curso = Curso::firstOrCreate(
            ['nombre' => 'Laravel + Vue.js - Nivel Intermedio'],
            [
                'descripcion' => 'Curso de desarrollo web con Laravel y Vue.js',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'fecha_inicio' => Carbon::now()->addDays(7),
                'fecha_fin' => Carbon::now()->addDays(30),
                'duracion_horas' => 40,
                'cupos_totales' => 25,
                'cupos_ocupados' => 0,
                'nivel' => 'Intermedio',
                'aula' => 'Lab 01',
                'activo' => true
            ]
        );eder;
use App\Models\TipoParticipante;
use App\Models\Participante;
use App\Models\Usuario;
use App\Models\Curso;
use App\Models\Preinscripcion;
use App\Models\Gestion;
use App\Models\PrecioCurso;
use Carbon\Carbon;

class DatosPruebaSeeder extends Seeder
{
    public function run()
    {
        // Crear gestión si no existe
        $gestion = Gestion::firstOrCreate([
            'nombre' => 'Gestión 2025',
            'descripcion' => 'Gestión académica 2025',
            'fecha_inicio' => Carbon::now()->startOfYear(),
            'fecha_fin' => Carbon::now()->endOfYear(),
            'activo' => true
        ]);

        // Crear tipos de participante si no existen
        $tipoEstudiante = TipoParticipante::firstOrCreate(['descripcion' => 'ESTUDIANTE'], ['codigo' => 'EST', 'activo' => true]);
        $tipoDocente = TipoParticipante::firstOrCreate(['descripcion' => 'DOCENTE'], ['codigo' => 'DOC', 'activo' => true]);
        $tipoExterno = TipoParticipante::firstOrCreate(['descripcion' => 'EXTERNO'], ['codigo' => 'EXT', 'activo' => true]);

        // Crear tutor (usuario responsable)
        $tutor = Usuario::firstOrCreate(
            ['email' => 'tutor.prueba@cicit.uagrm.edu.bo'],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodriguez',
                'carnet' => 'TUT001',
                'telefono' => '70123456',
                'password' => bcrypt('password'),
                'rol' => 'RESPONSABLE',
                'registro' => 'CICIT999',
                'activo' => true,
                'email_verified_at' => now()
            ]
        );

        // Crear curso de prueba
        $curso = Curso::firstOrCreate(
            ['nombre' => 'Laravel + Vue.js - Nivel Intermedio'],
            [
                'descripcion' => 'Curso de desarrollo web con Laravel y Vue.js',
                'tutor_id' => $tutor->id,
                'gestion_id' => $gestion->id,
                'fecha_inicio' => Carbon::now()->addDays(7),
                'fecha_fin' => Carbon::now()->addDays(30),
                'duracion_horas' => 40,
                'cupos_totales' => 25,
                'cupos_ocupados' => 0,
                'nivel' => 'Intermedio',
                'aula' => 'Lab 01',
                'activo' => true
            ]
        );

        // Crear precios para el curso
        PrecioCurso::firstOrCreate([
            'curso_id' => $curso->id,
            'tipo_participante_id' => $tipoEstudiante->id,
            'precio' => 200.00
        ]);

        PrecioCurso::firstOrCreate([
            'curso_id' => $curso->id,
            'tipo_participante_id' => $tipoDocente->id,
            'precio' => 250.00
        ]);

        PrecioCurso::firstOrCreate([
            'curso_id' => $curso->id,
            'tipo_participante_id' => $tipoExterno->id,
            'precio' => 350.00
        ]);

        // Crear participantes de prueba
        $participantes = [
            [
                'nombre' => 'Juan Carlos',
                'apellido' => 'Pérez López',
                'carnet' => '12345678',
                'email' => 'juan.perez@estudiante.uagrm.edu.bo',
                'telefono' => '70111111',
                'tipo_participante_id' => $tipoEstudiante->id
            ],
            [
                'nombre' => 'María Elena',
                'apellido' => 'González Vargas',
                'carnet' => '87654321',
                'email' => 'maria.gonzalez@uagrm.edu.bo',
                'telefono' => '70222222',
                'tipo_participante_id' => $tipoDocente->id
            ],
            [
                'nombre' => 'Roberto',
                'apellido' => 'Martínez Silva',
                'carnet' => '11223344',
                'email' => 'roberto.martinez@gmail.com',
                'telefono' => '70333333',
                'tipo_participante_id' => $tipoExterno->id
            ]
        ];

        foreach ($participantes as $participanteData) {
            $participante = Participante::firstOrCreate(
                ['carnet' => $participanteData['carnet']],
                $participanteData
            );

            // Crear preinscripción aprobada para cada participante
            Preinscripcion::firstOrCreate([
                'participante_id' => $participante->id,
                'curso_id' => $curso->id,
                'fecha_preinscripcion' => Carbon::now()->subDays(rand(1, 7)),
                'estado' => 'APROBADA',
                'observaciones' => 'Preinscripción aprobada para pruebas'
            ]);
        }

        $this->command->info('Datos de prueba creados exitosamente:');
        $this->command->info('- Curso: ' . $curso->nombre);
        $this->command->info('- Participantes: ' . count($participantes));
        $this->command->info('- Preinscripciones: ' . count($participantes) . ' (todas APROBADAS)');
        $this->command->info('');
        $this->command->info('Para probar el flujo:');
        $this->command->info('1. Ingrese como admin@cicit.uagrm.edu.bo / password');
        $this->command->info('2. Vaya a Admin > Pagos > Registrar Pago');
        $this->command->info('3. Use IDs de preinscripción: 1, 2, 3');
    }
}
