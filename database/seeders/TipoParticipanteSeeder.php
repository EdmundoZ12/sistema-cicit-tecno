<?php

namespace Database\Seeders;

use App\Models\TipoParticipante;
use Illuminate\Database\Seeder;

class TipoParticipanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => 'EST_FICCT',
                'descripcion' => 'Estudiante FICCT (Ingeniería en Ciencias de la Computación y Telecomunicaciones)',
                'activo' => true,
            ],
            [
                'codigo' => 'EST_UAGRM',
                'descripcion' => 'Estudiante UAGRM (Universidad Autónoma Gabriel René Moreno)',
                'activo' => true,
            ],
            [
                'codigo' => 'PARTICULAR',
                'descripcion' => 'Participante Particular (Externo)',
                'activo' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoParticipante::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
