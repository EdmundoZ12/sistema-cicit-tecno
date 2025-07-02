<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResponsableSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario responsable si no existe
        Usuario::firstOrCreate(
            ['registro' => 'admin'],
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'carnet' => '0000000',
                'email' => 'admin@cicit.com',
                'telefono' => '70000000',
                'password' => Hash::make('admin123'),
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ]
        );

        echo "Usuario responsable creado: admin / admin123\n";
    }
}
