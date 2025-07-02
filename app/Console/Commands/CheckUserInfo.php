<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;

class CheckUserInfo extends Command
{
    protected $signature = 'user:check';
    protected $description = 'Check user information';

    public function handle()
    {
        $user = Usuario::where('registro', '2210')->first();

        if ($user) {
            $this->info("Usuario encontrado:");
            $this->info("ID: {$user->id}");
            $this->info("Registro: {$user->registro}");
            $this->info("Nombre: {$user->nombre} {$user->apellido}");
            $this->info("Email: {$user->email}");
        } else {
            $this->error('Usuario no encontrado');
        }

        return 0;
    }
}
