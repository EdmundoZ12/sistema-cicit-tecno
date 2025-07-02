<?php

namespace App\Http\Controllers\Compartido;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Obtener menú dinámico según el rol del usuario
     */
    public function obtenerMenu()
    {
        $user = Auth::user();

        if (!$user) {
            // Menú para usuarios no autenticados
            return response()->json([
                'menu' => $this->getMenuPublico(),
                'usuario' => null,
            ]);
        }

        // Cache del menú por rol (5 minutos)
        $cacheKey = "menu_dinamico_{$user->rol}";

        $menu = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->getMenuPorRol($user->rol);
        });

        return response()->json([
            'menu' => $menu,
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre_completo,
                'rol' => $user->rol,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Obtener estructura del menú para el rol específico
     */
    private function getMenuPorRol($rol)
    {
        // Obtener elementos del menú para el rol específico
        $items = MenuItem::where('activo', true)
            ->where(function ($query) use ($rol) {
                $query->where('rol', $rol)
                    ->orWhere('rol', 'TODOS');
            })
            ->orderBy('orden')
            ->get();

        return $this->construirEstructuraMenu($items);
    }

    /**
     * Obtener menú público (sin autenticación)
     */
    private function getMenuPublico()
    {
        $menuPublico = [
            [
                'id' => 'home',
                'titulo' => 'CICIT',
                'ruta' => '/',
                'icono' => 'home',
                'orden' => 1,
                'activo' => true,
                'hijos' => [],
            ],
            [
                'id' => 'cursos',
                'titulo' => 'Cursos de Certificación',
                'ruta' => '/cursos',
                'icono' => 'academic-cap',
                'orden' => 2,
                'activo' => true,
                'hijos' => [],
            ],
            [
                'id' => 'verificar',
                'titulo' => 'Verificar Certificado',
                'ruta' => '/certificados/verificar',
                'icono' => 'shield-check',
                'orden' => 3,
                'activo' => true,
                'hijos' => [],
            ],
            [
                'id' => 'preinscripcion',
                'titulo' => 'Preinscripción',
                'ruta' => '/preinscripcion',
                'icono' => 'document-add',
                'orden' => 4,
                'activo' => true,
                'hijos' => [],
            ],
            [
                'id' => 'auth',
                'titulo' => 'Acceso al Sistema',
                'ruta' => null,
                'icono' => 'login',
                'orden' => 5,
                'activo' => true,
                'hijos' => [
                    [
                        'id' => 'login',
                        'titulo' => 'Iniciar Sesión',
                        'ruta' => '/login',
                        'icono' => 'login',
                        'orden' => 1,
                        'activo' => true,
                    ],
                    [
                        'id' => 'register',
                        'titulo' => 'Registro',
                        'ruta' => '/register',
                        'icono' => 'user-add',
                        'orden' => 2,
                        'activo' => true,
                    ],
                ],
            ],
        ];

        return $menuPublico;
    }

    /**
     * Construir estructura jerárquica del menú
     */
    private function construirEstructuraMenu($items)
    {
        $menu = [];
        $itemsPorId = [];

        // Crear array indexado por ID para fácil acceso
        foreach ($items as $item) {
            $itemsPorId[$item->id] = [
                'id' => $item->id,
                'titulo' => $item->titulo,
                'ruta' => $item->ruta,
                'icono' => $item->icono,
                'orden' => $item->orden,
                'padre_id' => $item->padre_id,
                'rol' => $item->rol,
                'activo' => $item->activo,
                'hijos' => [],
            ];
        }

        // Construir estructura jerárquica
        foreach ($itemsPorId as $item) {
            if ($item['padre_id'] === null) {
                // Es un elemento raíz
                $menu[] = &$itemsPorId[$item['id']];
            } else {
                // Es un hijo, agregarlo al padre correspondiente
                if (isset($itemsPorId[$item['padre_id']])) {
                    $itemsPorId[$item['padre_id']]['hijos'][] = &$itemsPorId[$item['id']];
                }
            }
        }

        // Ordenar hijos de cada elemento
        foreach ($menu as &$item) {
            if (!empty($item['hijos'])) {
                usort($item['hijos'], function ($a, $b) {
                    return $a['orden'] - $b['orden'];
                });
            }
        }

        return $menu;
    }

    /**
     * Obtener breadcrumbs para una ruta específica
     */
    public function obtenerBreadcrumbs(Request $request)
    {
        $validated = $request->validate([
            'ruta' => ['required', 'string'],
        ]);

        $rutaActual = $validated['ruta'];
        $user = Auth::user();

        if (!$user) {
            return response()->json(['breadcrumbs' => []]);
        }

        // Buscar el item del menú que corresponde a la ruta actual
        $items = MenuItem::where('activo', true)
            ->where(function ($query) use ($user) {
                $query->where('rol', $user->rol)
                    ->orWhere('rol', 'TODOS');
            })
            ->get();

        $itemActual = $items->firstWhere('ruta', $rutaActual);

        if (!$itemActual) {
            return response()->json(['breadcrumbs' => []]);
        }

        $breadcrumbs = $this->construirBreadcrumbs($itemActual, $items);

        return response()->json(['breadcrumbs' => $breadcrumbs]);
    }

    /**
     * Construir breadcrumbs desde el item actual hasta la raíz
     */
    private function construirBreadcrumbs($itemActual, $todosLosItems)
    {
        $breadcrumbs = [];
        $item = $itemActual;

        while ($item) {
            array_unshift($breadcrumbs, [
                'titulo' => $item->titulo,
                'ruta' => $item->ruta,
                'activo' => $item->id === $itemActual->id,
            ]);

            // Buscar el padre
            if ($item->padre_id) {
                $item = $todosLosItems->firstWhere('id', $item->padre_id);
            } else {
                $item = null;
            }
        }

        // Agregar "Dashboard" al inicio si el usuario está autenticado
        if (!empty($breadcrumbs)) {
            array_unshift($breadcrumbs, [
                'titulo' => 'Dashboard',
                'ruta' => '/dashboard',
                'activo' => false,
            ]);
        }

        return $breadcrumbs;
    }

    /**
     * Obtener estadísticas del menú (para RESPONSABLE)
     */
    public function estadisticasMenu()
    {
        if (!Auth::check() || Auth::user()->rol !== 'RESPONSABLE') {
            abort(403, 'No autorizado');
        }

        $stats = [
            'total_items' => MenuItem::count(),
            'items_activos' => MenuItem::where('activo', true)->count(),
            'items_por_rol' => MenuItem::select('rol', DB::raw('COUNT(*) as total'))
                ->groupBy('rol')
                ->get(),
            'items_con_hijos' => MenuItem::whereHas('hijos')->count(),
            'items_huerfanos' => MenuItem::whereNull('padre_id')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Limpiar cache del menú
     */
    public function limpiarCacheMenu()
    {
        if (!Auth::check() || Auth::user()->rol !== 'RESPONSABLE') {
            abort(403, 'No autorizado');
        }

        $roles = ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'];

        foreach ($roles as $rol) {
            Cache::forget("menu_dinamico_{$rol}");
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Cache del menú limpiado exitosamente.',
        ]);
    }

    /**
     * Verificar si el usuario tiene acceso a una ruta específica
     */
    public function verificarAcceso(Request $request)
    {
        $validated = $request->validate([
            'ruta' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $ruta = $validated['ruta'];

        if (!$user) {
            // Verificar si es una ruta pública
            $rutasPublicas = ['/', '/cursos', '/certificados/verificar', '/preinscripcion', '/login', '/register'];
            return response()->json(['tiene_acceso' => in_array($ruta, $rutasPublicas)]);
        }

        // Verificar si el usuario tiene un item de menú que corresponde a esta ruta
        $tieneAcceso = MenuItem::where('activo', true)
            ->where('ruta', $ruta)
            ->where(function ($query) use ($user) {
                $query->where('rol', $user->rol)
                    ->orWhere('rol', 'TODOS');
            })
            ->exists();

        return response()->json(['tiene_acceso' => $tieneAcceso]);
    }
}
