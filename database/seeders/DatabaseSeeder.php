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

            // 3. Después el menú
            MenuItemSeeder::class,

            // 4. Finalmente las estadísticas
            EstadisticaSeeder::class,
        ]);
    }
}
