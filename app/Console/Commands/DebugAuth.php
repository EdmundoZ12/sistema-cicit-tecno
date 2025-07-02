<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class DebugAuth extends Command
{
    protected $signature = 'auth:debug';
    protected $description = 'Debug authentication state';

    public function handle()
    {
        $this->info('Verificando estado de autenticación...');

        // Verificar usuarios en la base de datos
        $usuarios = Usuario::all();
        $this->info("Total usuarios en BD: " . $usuarios->count());

        foreach ($usuarios as $user) {
            $this->info("ID: {$user->id}, Registro: {$user->registro}, Nombre: {$user->nombre}");
        }

        return 0;
    }
}
