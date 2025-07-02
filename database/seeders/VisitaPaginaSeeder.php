<?php

namespace Database\Seeders;

use App\Models\VisitaPagina;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VisitaPaginaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::all();
        
        // Definir páginas principales del sistema
        $paginas = [
            '/',
            '/cursos',
            '/sobre-nosotros',
            '/contacto',
            '/verificar-certificado',
            '/dashboard',
            '/responsable/dashboard',
            '/administrativo/dashboard',
            '/tutor/dashboard',
            '/responsable/usuarios',
            '/responsable/cursos',
            '/responsable/preinscripciones',
            '/responsable/inscripciones',
            '/buscar',
        ];

        // Generar IPs realistas para visitantes anónimos
        $ipsVisitantes = [
            '192.168.1.10', '192.168.1.15', '192.168.1.20', '192.168.1.25',
            '10.0.0.5', '10.0.0.12', '10.0.0.18', '10.0.0.22',
            '172.16.0.8', '172.16.0.14', '172.16.0.19', '172.16.0.23',
            '190.123.45.67', '201.234.56.78', '186.45.67.89', '179.56.78.90',
        ];

        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        $visitasGeneradas = [];

        // Generar 500 visitas en los últimos 60 días
        for ($i = 0; $i < 500; $i++) {
            $fechaVisita = fake()->dateTimeBetween('-60 days', 'now');
            
            // 30% usuarios autenticados, 70% anónimos
            $usuario = null;
            if (rand(1, 100) <= 30 && $usuarios->count() > 0) {
                $usuario = $usuarios->random();
            }

            $visita = [
                'pagina' => $paginas[array_rand($paginas)],
                'ip_address' => $usuario ? '127.0.0.1' : $ipsVisitantes[array_rand($ipsVisitantes)],
                'user_agent' => $userAgents[array_rand($userAgents)],
                'usuario_id' => $usuario?->id,
                'session_id' => 'sess_' . uniqid(),
                'fecha_visita' => $fechaVisita,
                'created_at' => $fechaVisita,
                'updated_at' => $fechaVisita,
            ];

            $visitasGeneradas[] = $visita;
        }

        // Insertar visitas en lotes para mejor rendimiento
        $chunks = array_chunk($visitasGeneradas, 100);
        foreach ($chunks as $chunk) {
            VisitaPagina::insert($chunk);
        }
    }
}
