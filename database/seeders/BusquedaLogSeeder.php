<?php

namespace Database\Seeders;

use App\Models\BusquedaLog;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BusquedaLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::all();
        
        // Términos de búsqueda realistas relacionados con CICIT
        $terminosBusqueda = [
            // Tecnologías y lenguajes
            'php', 'laravel', 'javascript', 'vue', 'react', 'python', 'mysql', 'css', 'html',
            'nodejs', 'typescript', 'angular', 'bootstrap', 'jquery', 'sql', 'mongodb',
            'git', 'github', 'docker', 'kubernetes', 'aws', 'firebase',
            
            // Conceptos de programación
            'programación', 'desarrollo web', 'frontend', 'backend', 'fullstack', 'api',
            'base de datos', 'algoritmos', 'estructura de datos', 'poo', 'mvc',
            'responsive design', 'ui ux', 'diseño web', 'mobile first',
            
            // Cursos específicos
            'curso php', 'curso javascript', 'curso python', 'curso base datos',
            'desarrollo web', 'programación web', 'diseño interfaces',
            'data science', 'análisis datos', 'machine learning',
            
            // Certificaciones y estudios
            'certificado', 'certificación', 'diploma', 'curso online', 'capacitación',
            'entrenamiento', 'workshop', 'seminario', 'bootcamp',
            
            // Términos generales
            'tecnología', 'informática', 'computación', 'sistemas', 'software',
            'aplicaciones', 'plataforma', 'herramientas', 'metodologías',
            'proyectos', 'prácticas', 'ejercicios', 'ejemplos',
            
            // Términos específicos de la industria
            'inteligencia artificial', 'blockchain', 'ciberseguridad', 'cloud computing',
            'devops', 'agile', 'scrum', 'testing', 'qa', 'automatización',
            
            // Búsquedas mal escritas (realista)
            'progamacion', 'javascipt', 'phyton', 'databse', 'curso gratis',
            'aprende rapido', 'tutorial', 'como hacer', 'guia',
        ];

        // IPs para búsquedas anónimas
        $ipsVisitantes = [
            '192.168.1.10', '192.168.1.15', '192.168.1.20', '192.168.1.25',
            '10.0.0.5', '10.0.0.12', '10.0.0.18', '10.0.0.22',
            '190.123.45.67', '201.234.56.78', '186.45.67.89', '179.56.78.90',
        ];

        $busquedasGeneradas = [];

        // Generar búsquedas de los últimos 30 días
        $fechaInicio = Carbon::now()->subDays(30);
        $fechaFin = Carbon::now();

        for ($fecha = $fechaInicio; $fecha <= $fechaFin; $fecha->addDay()) {
            // Más búsquedas en días laborales
            $factorDia = $fecha->isWeekend() ? 0.4 : 1.0;
            
            // Número de búsquedas por día
            $busquedasPorDia = rand(5, 25) * $factorDia;

            for ($i = 0; $i < $busquedasPorDia; $i++) {
                // Hora aleatoria (más durante horario laboral/estudio)
                $hora = rand(0, 23);
                $probabilidadHorario = ($hora >= 9 && $hora <= 22) ? 0.8 : 0.2;
                
                if (rand(1, 100) / 100 <= $probabilidadHorario) {
                    $fechaBusqueda = $fecha->copy()->setTime($hora, rand(0, 59), rand(0, 59));

                    // Usuario (20% autenticados, 80% anónimos)
                    $usuario = null;
                    if (rand(1, 100) <= 20 && $usuarios->count() > 0) {
                        $usuario = $usuarios->random();
                    }

                    // Seleccionar término de búsqueda
                    $termino = $this->seleccionarTerminoConPeso($terminosBusqueda);
                    
                    // Simular resultados encontrados
                    $resultadosEncontrados = $this->simularResultados($termino);

                    $busqueda = [
                        'termino' => $termino,
                        'resultados' => $resultadosEncontrados,
                        'ip_address' => $usuario ? '127.0.0.1' : $ipsVisitantes[array_rand($ipsVisitantes)],
                        'usuario_id' => $usuario?->id,
                        'fecha_busqueda' => $fechaBusqueda,
                        'created_at' => $fechaBusqueda,
                        'updated_at' => $fechaBusqueda,
                    ];

                    $busquedasGeneradas[] = $busqueda;
                }
            }
        }

        // Insertar búsquedas en lotes
        $chunks = array_chunk($busquedasGeneradas, 50);
        foreach ($chunks as $chunk) {
            BusquedaLog::insert($chunk);
        }
    }

    /**
     * Seleccionar término con peso (términos más comunes tienen mayor probabilidad)
     */
    private function seleccionarTerminoConPeso($terminos): string
    {
        // Términos más populares
        $terminosPopulares = [
            'php', 'javascript', 'python', 'laravel', 'vue', 'programación',
            'desarrollo web', 'curso', 'certificado', 'base de datos'
        ];

        // 60% de probabilidad de usar término popular
        if (rand(1, 100) <= 60) {
            $terminoPopular = $terminosPopulares[array_rand($terminosPopulares)];
            
            // Algunas veces combinan términos
            if (rand(1, 100) <= 30) {
                $segundo = $terminos[array_rand($terminos)];
                return "$terminoPopular $segundo";
            }
            
            return $terminoPopular;
        }

        // 40% término aleatorio
        $termino = $terminos[array_rand($terminos)];
        
        // 20% de probabilidad de búsqueda con múltiples términos
        if (rand(1, 100) <= 20) {
            $segundo = $terminos[array_rand($terminos)];
            return "$termino $segundo";
        }

        return $termino;
    }

    /**
     * Simular número de resultados según el término
     */
    private function simularResultados($termino): int
    {
        $termino = strtolower($termino);
        
        // Términos que deberían tener muchos resultados
        $terminosPopulares = ['php', 'javascript', 'python', 'programación', 'curso', 'desarrollo'];
        
        foreach ($terminosPopulares as $popular) {
            if (strpos($termino, $popular) !== false) {
                return rand(50, 200);
            }
        }
        
        // Términos muy específicos
        if (strlen($termino) > 20 || str_word_count($termino) > 3) {
            return rand(0, 15);
        }
        
        // Términos normales
        return rand(5, 80);
    }

    /**
     * Generar User Agent realista
     */
    private function generarUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 14; Mobile; rv:121.0) Gecko/121.0 Firefox/121.0',
        ];

        return $userAgents[array_rand($userAgents)];
    }

    /**
     * Generar filtros aplicados ocasionalmente
     */
    private function generarFiltros(): ?string
    {
        // 70% sin filtros
        if (rand(1, 100) <= 70) {
            return null;
        }

        $filtros = [
            'modalidad:presencial',
            'modalidad:virtual',
            'modalidad:hibrida',
            'estado:programado',
            'estado:en_curso',
            'certificacion:si',
            'duracion:30-50',
            'precio:0-200',
            'nivel:basico',
            'nivel:intermedio',
        ];

        // 80% un filtro, 20% múltiples filtros
        if (rand(1, 100) <= 80) {
            return $filtros[array_rand($filtros)];
        } else {
            $filtrosSeleccionados = array_rand($filtros, rand(2, 3));
            if (is_array($filtrosSeleccionados)) {
                return implode(',', array_map(fn($i) => $filtros[$i], $filtrosSeleccionados));
            } else {
                return $filtros[$filtrosSeleccionados];
            }
        }
    }
}
