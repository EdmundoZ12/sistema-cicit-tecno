<?php

namespace Database\Seeders;

use App\Models\TemaConfiguracion;
use Illuminate\Database\Seeder;

class TemaConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $temas = [
            [
                'nombre' => 'Estudiantes Jóvenes',
                'descripcion' => 'Tema vibrante con colores de la Facultad FICCT',
                'color_primario' => '#1565C0',
                'color_secundario' => '#D32F2F',
                'color_fondo' => '#E3F2FD',
                'color_texto' => '#0D47A1',
                'tamano_fuente_base' => 16,
                'alto_contraste' => false,
                'target_edad' => 'jovenes',
                'modo_oscuro' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Profesionales',
                'descripcion' => 'Tema elegante con identidad institucional FICCT',
                'color_primario' => '#0D47A1',
                'color_secundario' => '#C62828',
                'color_fondo' => '#FAFAFA',
                'color_texto' => '#1A237E',
                'tamano_fuente_base' => 16,
                'alto_contraste' => false,
                'target_edad' => 'adultos',
                'modo_oscuro' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Accesibilidad',
                'descripcion' => 'Tema con alto contraste y colores institucionales',
                'color_primario' => '#D32F2F',
                'color_secundario' => '#1565C0',
                'color_fondo' => '#FFF3E0',
                'color_texto' => '#BF360C',
                'tamano_fuente_base' => 18,
                'alto_contraste' => true,
                'target_edad' => 'adultos',
                'modo_oscuro' => false,
                'activo' => true,
            ],
            [
                'nombre' => 'Modo Nocturno',
                'descripcion' => 'Tema oscuro con acentos de la Facultad',
                'color_primario' => '#1976D2',
                'color_secundario' => '#F44336',
                'color_fondo' => '#212121',
                'color_texto' => '#E3F2FD',
                'tamano_fuente_base' => 16,
                'alto_contraste' => false,
                'target_edad' => 'adultos',
                'modo_oscuro' => true,
                'activo' => true,
            ],
        ];

        foreach ($temas as $tema) {
            TemaConfiguracion::create($tema);
        }
    }
}
