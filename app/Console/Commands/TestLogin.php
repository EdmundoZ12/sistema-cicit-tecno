<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TestLogin extends Command
{
    protected $signature = 'test:login';
    protected $description = 'Test login functionality';

    public function handle()
    {
        $this->info('Probando funcionalidad de login...');

        // Encontrar el usuario
        $user = Usuario::where('registro', '2210')->first();

        if (!$user) {
            $this->error('Usuario no encontrado');
            return 1;
        }

        $this->info("Usuario encontrado: ID={$user->id}, Registro={$user->registro}");

        // Probar autenticación
        $credentials = [
            'registro' => '2210',
            'password' => 'juanito',
            'activo' => true
        ];

        if (Auth::attempt($credentials)) {
            $this->info('✅ Autenticación exitosa');
            $this->info('Auth::id() = ' . Auth::id());
            $this->info('Auth::user()->id = ' . Auth::user()->id);
            $this->info('Auth::user()->registro = ' . Auth::user()->registro);

            // Cerrar sesión
            Auth::logout();
        } else {
            $this->error('❌ Falló la autenticación');

            // Verificar contraseña
            if (Hash::check('juanito', $user->password)) {
                $this->info('✅ Contraseña es correcta');
            } else {
                $this->error('❌ Contraseña incorrecta');
            }
        }

        return 0;
    }
}
