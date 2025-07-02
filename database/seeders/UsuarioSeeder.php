<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Datos basados exactamente en el script SQL de CICIT
     */
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'Administrador',
                'apellido' => 'CICIT',
                'carnet' => 'ADMIN001',
                'email' => 'admin@cicit.uagrm.edu.bo',
                'telefono' => '70000000',
                'password' => Hash::make('cicit2025'),
                'rol' => 'RESPONSABLE',
                'registro' => 'CICIT001',
                'activo' => true,
                'email_verified_at' => now(),
            ],
            [
                'nombre' => 'María Elena',
                'apellido' => 'Vargas Rojas',
                'carnet' => 'ADM001',
                'email' => 'mvargas@cicit.uagrm.edu.bo',
                'telefono' => '70234567',
                'password' => Hash::make('admin123'),
                'rol' => 'ADMINISTRATIVO',
                'registro' => 'CICIT002',
                'activo' => true,
                'email_verified_at' => now(),
            ],
            [
                'nombre' => 'Roberto',
                'apellido' => 'Chávez Morales',
                'carnet' => 'TUT001',
                'email' => 'rchavez@cicit.uagrm.edu.bo',
                'telefono' => '70345678',
                'password' => Hash::make('tutor123'),
                'rol' => 'TUTOR',
                'registro' => 'CICIT003',
                'activo' => true,
                'email_verified_at' => now(),
            ]
        ];

        foreach ($usuarios as $usuario) {
            Usuario::create($usuario);
        }
    }
}
