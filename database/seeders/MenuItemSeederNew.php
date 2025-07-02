<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla existente
        MenuItem::truncate();

        $menuItems = [
            // Menú general (visible para todos los usuarios del sistema)
            [
                'titulo' => 'Dashboard',
                'ruta' => '/',
                'icono' => 'home',
                'orden' => 1,
                'rol' => 'TODOS',
                'activo' => true,
            ],
            [
                'titulo' => 'Cursos de Certificación',
                'ruta' => '/cursos',
                'icono' => 'academic-cap',
                'orden' => 2,
                'rol' => 'TODOS',
                'activo' => true,
            ],
            [
                'titulo' => 'Mi Perfil',
                'ruta' => '/perfil',
                'icono' => 'user-circle',
                'orden' => 9,
                'rol' => 'TODOS',
                'activo' => true,
            ],

            // Menú para RESPONSABLE (Administración general del CICIT)
            [
                'titulo' => 'Gestión de Usuarios',
                'ruta' => '/usuarios',
                'icono' => 'users',
                'orden' => 3,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Gestiones Académicas',
                'ruta' => '/gestiones',
                'icono' => 'calendar',
                'orden' => 4,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Administrar Cursos',
                'ruta' => '/admin-cursos',
                'icono' => 'cog',
                'orden' => 5,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Reportes Institucionales',
                'ruta' => '/reportes',
                'icono' => 'chart-bar',
                'orden' => 6,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Estadísticas CICIT',
                'ruta' => '/estadisticas',
                'icono' => 'trending-up',
                'orden' => 7,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Gestión de Certificados',
                'ruta' => '/admin-certificados',
                'icono' => 'certificate',
                'orden' => 8,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],
            [
                'titulo' => 'Configuración del Sistema',
                'ruta' => '/configuracion',
                'icono' => 'settings',
                'orden' => 10,
                'rol' => 'RESPONSABLE',
                'activo' => true,
            ],

            // Menú para ADMINISTRATIVO (Operaciones de inscripción y pagos)
            [
                'titulo' => 'Gestión de Participantes',
                'ruta' => '/participantes',
                'icono' => 'user-group',
                'orden' => 3,
                'rol' => 'ADMINISTRATIVO',
                'activo' => true,
            ],
            [
                'titulo' => 'Preinscripciones',
                'ruta' => '/preinscripciones',
                'icono' => 'clipboard-list',
                'orden' => 4,
                'rol' => 'ADMINISTRATIVO',
                'activo' => true,
            ],
            [
                'titulo' => 'Inscripciones Oficiales',
                'ruta' => '/inscripciones',
                'icono' => 'clipboard-check',
                'orden' => 5,
                'rol' => 'ADMINISTRATIVO',
                'activo' => true,
            ],
            [
                'titulo' => 'Control de Pagos',
                'ruta' => '/pagos',
                'icono' => 'credit-card',
                'orden' => 6,
                'rol' => 'ADMINISTRATIVO',
                'activo' => true,
            ],
            [
                'titulo' => 'Emisión de Certificados',
                'ruta' => '/certificados',
                'icono' => 'academic-cap',
                'orden' => 7,
                'rol' => 'ADMINISTRATIVO',
                'activo' => true,
            ],

            // Menú para TUTOR (Gestión de cursos asignados)
            [
                'titulo' => 'Mis Cursos CICIT',
                'ruta' => '/mis-cursos',
                'icono' => 'book-open',
                'orden' => 3,
                'rol' => 'TUTOR',
                'activo' => true,
            ],
            [
                'titulo' => 'Gestión de Tareas',
                'ruta' => '/tareas',
                'icono' => 'document-text',
                'orden' => 4,
                'rol' => 'TUTOR',
                'activo' => true,
            ],
            [
                'titulo' => 'Control de Asistencia',
                'ruta' => '/asistencias',
                'icono' => 'clipboard-check',
                'orden' => 5,
                'rol' => 'TUTOR',
                'activo' => true,
            ],
            [
                'titulo' => 'Registro de Notas',
                'ruta' => '/notas',
                'icono' => 'pencil-alt',
                'orden' => 6,
                'rol' => 'TUTOR',
                'activo' => true,
            ],
            [
                'titulo' => 'Participantes del Curso',
                'ruta' => '/mis-participantes',
                'icono' => 'users',
                'orden' => 7,
                'rol' => 'TUTOR',
                'activo' => true,
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}
