<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Orden específico según dependencias del script SQL de CICIT
     */
    public function run(): void
    {
        $this->call([
            // 1. Primero los datos base (sin dependencias)
            TipoParticipanteSeeder::class,
            TemaConfiguracionSeeder::class,
            ConfiguracionSitioSeeder::class,
            GestionSeeder::class,

            // 2. Luego los usuarios (dependen de TipoParticipante)
            UsuarioSeeder::class,

            // 3. Después cursos (dependen de usuarios y gestión)
            CursoSeeder::class,
            PrecioCursoSeeder::class,

            // 4. Participantes (dependen de TipoParticipante)
            ParticipanteSeeder::class,

            // 5. Preinscripciones e inscripciones (dependen de cursos y participantes)
            PreinscripcionSeeder::class,
            InscripcionSeeder::class,

            // 6. El menú
            MenuItemSeeder::class,

            // 7. Estadísticas y logs (dependen de usuarios y estructura general)
            VisitaPaginaSeeder::class,
            BusquedaLogSeeder::class,
            EstadisticaSeeder::class,
        ]);
    }
}
