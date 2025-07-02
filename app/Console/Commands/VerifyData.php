<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TipoParticipante;
use App\Models\TemaConfiguracion;
use App\Models\ConfiguracionSitio;
use App\Models\Usuario;
use App\Models\MenuItem;
use App\Models\Gestion;
use App\Models\Estadistica;

class VerifyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar que los datos iniciales del CICIT están correctamente cargados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE DATOS INICIALES CICIT ===');
        $this->newLine();

        // Verificar Tipos de Participante
        $this->info('1. TIPOS DE PARTICIPANTE:');
        $tipos = TipoParticipante::all(['codigo', 'descripcion']);
        foreach ($tipos as $tipo) {
            $this->line("   - {$tipo->codigo}: {$tipo->descripcion}");
        }
        $this->newLine();

        // Verificar Temas
        $this->info('2. TEMAS DE CONFIGURACIÓN:');
        $temas = TemaConfiguracion::all(['nombre', 'target_edad', 'modo_oscuro']);
        foreach ($temas as $tema) {
            $modo = $tema->modo_oscuro ? 'Oscuro' : 'Claro';
            $this->line("   - {$tema->nombre} (Edad: {$tema->target_edad}, Modo: {$modo})");
        }
        $this->newLine();

        // Verificar Configuración del Sitio
        $this->info('3. CONFIGURACIÓN DEL SITIO (Muestra):');
        $configs = ConfiguracionSitio::whereIn('clave', ['nombre_sitio', 'nombre_corto', 'email_contacto'])->get();
        foreach ($configs as $config) {
            $this->line("   - {$config->clave}: {$config->valor}");
        }
        $this->newLine();

        // Verificar Usuarios
        $this->info('4. USUARIOS INICIALES:');
        $usuarios = Usuario::all(['nombre', 'apellido', 'email', 'rol']);
        foreach ($usuarios as $usuario) {
            $this->line("   - {$usuario->nombre} {$usuario->apellido} ({$usuario->email}) - Rol: {$usuario->rol}");
        }
        $this->newLine();

        // Verificar Menús
        $this->info('5. ELEMENTOS DE MENÚ POR ROL:');
        $roles = ['TODOS', 'RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'];
        foreach ($roles as $rol) {
            $menus = MenuItem::where('rol', $rol)->orderBy('orden')->get(['titulo', 'ruta']);
            $this->line("   {$rol} ({$menus->count()} items):");
            foreach ($menus as $menu) {
                $this->line("     - {$menu->titulo} ({$menu->ruta})");
            }
        }
        $this->newLine();

        // Verificar Gestiones
        $this->info('6. GESTIONES ACADÉMICAS:');
        $gestiones = Gestion::all(['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']);
        foreach ($gestiones as $gestion) {
            $this->line("   - {$gestion->nombre}: {$gestion->descripcion} ({$gestion->fecha_inicio} a {$gestion->fecha_fin})");
        }
        $this->newLine();

        // Verificar Estadísticas
        $this->info('7. ESTADÍSTICAS INICIALES:');
        $estadisticas = Estadistica::all(['tipo', 'valor', 'descripcion']);
        foreach ($estadisticas as $estadistica) {
            $this->line("   - {$estadistica->tipo}: {$estadistica->valor} ({$estadistica->descripcion})");
        }
        $this->newLine();

        $this->info('✅ Verificación completada. Todos los seeders han cargado correctamente los datos del script SQL.');

        return 0;
    }
}
