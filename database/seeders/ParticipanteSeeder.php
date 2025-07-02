<?php

namespace Database\Seeders;

use App\Models\Participante;
use App\Models\TipoParticipante;
use Illuminate\Database\Seeder;

class ParticipanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener tipos de participante
        $tipoFICCT = TipoParticipante::where('codigo', 'EST_FICCT')->first();
        $tipoUAGRM = TipoParticipante::where('codigo', 'EST_UAGRM')->first();
        $tipoParticular = TipoParticipante::where('codigo', 'PARTICULAR')->first();

        $participantes = [
            // Estudiantes FICCT
            [
                'nombre' => 'Ana María',
                'apellido' => 'Gonzáles Pérez',
                'carnet' => '201845123',
                'email' => 'ana.gonzales@est.uagrm.edu.bo',
                'telefono' => '70123456',
                'universidad' => 'Universidad Autónoma Gabriel René Moreno',
                'tipo_participante_id' => $tipoFICCT->id,
                'registro' => 'EST001',
                'activo' => true,
            ],
            [
                'nombre' => 'Carlos Eduardo',
                'apellido' => 'Mendoza Ruiz',
                'carnet' => '201934567',
                'email' => 'carlos.mendoza@est.uagrm.edu.bo',
                'telefono' => '70234567',
                'universidad' => 'Universidad Autónoma Gabriel René Moreno',
                'tipo_participante_id' => $tipoFICCT->id,
                'registro' => 'EST002',
                'activo' => true,
            ],
            [
                'nombre' => 'María José',
                'apellido' => 'Vargas Limachi',
                'carnet' => '202012345',
                'email' => 'maria.vargas@est.uagrm.edu.bo',
                'telefono' => '70345678',
                'universidad' => 'Universidad Autónoma Gabriel René Moreno',
                'tipo_participante_id' => $tipoFICCT->id,
                'registro' => 'EST003',
                'activo' => true,
            ],

            // Estudiantes UAGRM (otras facultades)
            [
                'nombre' => 'Roberto',
                'apellido' => 'Chávez Morales',
                'carnet' => '201723456',
                'email' => 'roberto.chavez@est.uagrm.edu.bo',
                'telefono' => '70456789',
                'universidad' => 'Universidad Autónoma Gabriel René Moreno',
                'tipo_participante_id' => $tipoUAGRM->id,
                'registro' => 'EST004',
                'activo' => true,
            ],
            [
                'nombre' => 'Daniela',
                'apellido' => 'Roca Sandoval',
                'carnet' => '201856789',
                'email' => 'daniela.roca@est.uagrm.edu.bo',
                'telefono' => '70567890',
                'universidad' => 'Universidad Autónoma Gabriel René Moreno',
                'tipo_participante_id' => $tipoUAGRM->id,
                'registro' => 'EST005',
                'activo' => true,
            ],

            // Participantes Particulares
            [
                'nombre' => 'Luis Fernando',
                'apellido' => 'Torrico Paz',
                'carnet' => '3456789',
                'email' => 'luis.torrico@gmail.com',
                'telefono' => '70678901',
                'universidad' => null,
                'tipo_participante_id' => $tipoParticular->id,
                'registro' => 'PAR001',
                'activo' => true,
            ],
            [
                'nombre' => 'Patricia Elena',
                'apellido' => 'Moreno Vaca',
                'carnet' => '4567890',
                'email' => 'patricia.moreno@hotmail.com',
                'telefono' => '70789012',
                'universidad' => null,
                'tipo_participante_id' => $tipoParticular->id,
                'registro' => 'PAR002',
                'activo' => true,
            ],
            [
                'nombre' => 'Javier Alejandro',
                'apellido' => 'Suárez Cabrera',
                'carnet' => '5678901',
                'email' => 'javier.suarez@yahoo.com',
                'telefono' => '70890123',
                'universidad' => null,
                'tipo_participante_id' => $tipoParticular->id,
                'registro' => 'PAR003',
                'activo' => true,
            ],
            [
                'nombre' => 'Andrea Lucía',
                'apellido' => 'Flores Gutiérrez',
                'carnet' => '6789012',
                'email' => 'andrea.flores@gmail.com',
                'telefono' => '70901234',
                'universidad' => null,
                'tipo_participante_id' => $tipoParticular->id,
                'registro' => 'PAR004',
                'activo' => true,
            ],
            [
                'nombre' => 'Fernando José',
                'apellido' => 'Quiroga Medina',
                'carnet' => '7890123',
                'email' => 'fernando.quiroga@outlook.com',
                'telefono' => '71012345',
                'universidad' => null,
                'tipo_participante_id' => $tipoParticular->id,
                'registro' => 'PAR005',
                'activo' => true,
            ],
        ];

        foreach ($participantes as $participante) {
            Participante::create($participante);
        }
    }
}
