<?php

namespace Database\Seeders;

use App\Models\Gestion;
use Illuminate\Database\Seeder;

class GestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gestiones = [
            [
                'nombre' => '2025-1',
                'descripcion' => 'Primer Semestre 2025',
                'fecha_inicio' => '2025-02-01',
                'fecha_fin' => '2025-06-30',
                'activo' => true,
            ]
        ];

        foreach ($gestiones as $gestion) {
            Gestion::create($gestion);
        }
    }
}
