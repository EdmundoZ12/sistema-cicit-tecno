<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use App\Models\ConfiguracionUsuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdmin extends Command
{
    protected $signature = 'admin:setup';
    protected $description = 'Setup admin user and theme configuration';

    public function handle()
    {
        $this->info('Configurando usuario administrador...');

        // Buscar o crear usuario admin
        $user = Usuario::where('registro', '2210')->first();

        if (!$user) {
            $this->error('Usuario con registro 2210 no encontrado.');
            return 1;
        }

        $this->info("Usuario encontrado: {$user->nombre} {$user->apellido}");
        $this->info("Registro: {$user->registro}");
        $this->info("Rol: {$user->rol}");
        $this->info("Activo: " . ($user->activo ? 'Sí' : 'No'));

        // Actualizar contraseña
        $user->password = Hash::make('juanito');
        $user->activo = true;
        $user->save();
        $this->info('Contraseña actualizada a: juanito');

        // Verificar o crear configuración de tema
        $config = $user->configuracion;

        if (!$config) {
            $this->info('Creando configuración de tema por defecto...');
            ConfiguracionUsuario::create([
                'usuario_id' => $user->id,
                'tema_id' => 1,
                'tamano_fuente' => 16,
                'alto_contraste' => false,
                'modo_automatico' => false,
            ]);
            $this->info('Configuración de tema creada con ID: 1');
        } else {
            $this->info("Configuración existente - Tema ID: {$config->tema_id}");
        }

        $this->info('✅ Usuario administrador configurado correctamente');
        $this->info('Credenciales: registro=2210, password=juanito');

        return 0;
    }
}
