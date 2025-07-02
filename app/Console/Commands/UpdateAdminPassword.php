<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UpdateAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar la contraseña del administrador CICIT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = Usuario::where('email', 'admin@cicit.uagrm.edu.bo')->first();

        if (!$admin) {
            $this->error('No se encontró el usuario admin@cicit.uagrm.edu.bo');
            return 1;
        }

        $admin->registro = '2210';
        $admin->password = Hash::make('juanito');
        $admin->save();

        $this->info('✅ Usuario actualizado para admin@cicit.uagrm.edu.bo');
        $this->info('Registro: 2210');
        $this->info('Password: juanito');

        return 0;
    }
}
