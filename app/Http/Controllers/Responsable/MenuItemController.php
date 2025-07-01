<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;

class MenuItemController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede gestionar el menú dinámico
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:RESPONSABLE']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $menuItems = MenuItem::query()
            ->with(['padre', 'hijos'])
            ->when($request->search, function ($query, $search) {
                $query->where('titulo', 'ILIKE', "%{$search}%")
                    ->orWhere('ruta', 'ILIKE', "%{$search}%");
            })
            ->when($request->rol, function ($query, $rol) {
                $query->where('rol', $rol);
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->when($request->tipo === 'principales', function ($query) {
                $query->principales();
            })
            ->when($request->tipo === 'submenu', function ($query) {
                $query->submenu();
            })
            ->orderBy('orden')
            ->paginate(20)
            ->withQueryString();

        // Obtener estructura jerárquica para vista en árbol
        $estructuraJerarquica = MenuItem::getEstructuraJerarquica();

        return Inertia::render('Responsable/MenuItems/Index', [
            'menuItems' => $menuItems,
            'estructuraJerarquica' => $estructuraJerarquica,
            'filters' => $request->only(['search', 'rol', 'activo', 'tipo']),
            'roles' => [
                'RESPONSABLE' => 'Responsable',
                'ADMINISTRATIVO' => 'Administrativo',
                'TUTOR' => 'Tutor',
                'TODOS' => 'Todos los Roles'
            ],
            'estadisticas' => [
                'total' => MenuItem::count(),
                'activos' => MenuItem::activo()->count(),
                'principales' => MenuItem::principales()->count(),
                'submenu' => MenuItem::submenu()->count(),
                'por_rol' => MenuItem::groupBy('rol')
                    ->selectRaw('rol, count(*) as total')
                    ->pluck('total', 'rol'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Responsable/MenuItems/Create', [
            'menuItemsPadre' => MenuItem::activo()->principales()->get(['id', 'titulo']),
            'roles' => [
                'RESPONSABLE' => 'Responsable',
                'ADMINISTRATIVO' => 'Administrativo',
                'TUTOR' => 'Tutor',
                'TODOS' => 'Todos los Roles'
            ],
            'iconos' => $this->getIconosDisponibles(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'ruta' => ['nullable', 'string', 'max:255'],
            'icono' => ['nullable', 'string', 'max:50'],
            'orden' => ['required', 'integer', 'min:1'],
            'padre_id' => ['nullable', 'exists:MENU_ITEM,id'],
            'rol' => ['nullable', Rule::in(['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR', 'TODOS'])],
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 100 caracteres.',
            'orden.required' => 'El orden es obligatorio.',
            'orden.min' => 'El orden debe ser mayor a 0.',
            'padre_id.exists' => 'El elemento padre seleccionado no es válido.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        // Validar que si tiene padre, debe tener ruta
        if ($validated['padre_id'] && empty($validated['ruta'])) {
            return back()->withErrors([
                'ruta' => 'Los elementos de submenú deben tener una ruta definida.'
            ])->withInput();
        }

        // Validar orden único dentro del mismo nivel
        $conflictoOrden = MenuItem::where('orden', $validated['orden'])
            ->where('padre_id', $validated['padre_id'])
            ->exists();

        if ($conflictoOrden) {
            return back()->withErrors([
                'orden' => 'Ya existe un elemento con este orden en el mismo nivel.'
            ])->withInput();
        }

        MenuItem::create([
            ...$validated,
            'activo' => true,
        ]);

        // Limpiar caché del menú
        $this->limpiarCacheMenu();

        return redirect()->route('responsable.menu-items.index')
            ->with('success', 'Elemento de menú creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MenuItem $menuItem): Response
    {
        $menuItem->load(['padre', 'hijosActivos']);

        // Obtener breadcrumbs
        $breadcrumbs = $menuItem->getBreadcrumbs();

        return Inertia::render('Responsable/MenuItems/Show', [
            'menuItem' => $menuItem,
            'breadcrumbs' => $breadcrumbs,
            'estadisticas' => [
                'nivel' => $menuItem->nivel,
                'tiene_hijos' => $menuItem->tiene_hijos,
                'total_hijos' => $menuItem->hijos()->count(),
                'hijos_activos' => $menuItem->hijosActivos()->count(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MenuItem $menuItem): Response
    {
        return Inertia::render('Responsable/MenuItems/Edit', [
            'menuItem' => $menuItem,
            'menuItemsPadre' => MenuItem::activo()
                ->principales()
                ->where('id', '!=', $menuItem->id) // Evitar auto-referencia
                ->get(['id', 'titulo']),
            'roles' => [
                'RESPONSABLE' => 'Responsable',
                'ADMINISTRATIVO' => 'Administrativo',
                'TUTOR' => 'Tutor',
                'TODOS' => 'Todos los Roles'
            ],
            'iconos' => $this->getIconosDisponibles(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MenuItem $menuItem)
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'ruta' => ['nullable', 'string', 'max:255'],
            'icono' => ['nullable', 'string', 'max:50'],
            'orden' => ['required', 'integer', 'min:1'],
            'padre_id' => ['nullable', 'exists:MENU_ITEM,id'],
            'rol' => ['nullable', Rule::in(['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR', 'TODOS'])],
            'activo' => ['boolean'],
        ], [
            'padre_id.exists' => 'El elemento padre seleccionado no es válido.',
        ]);

        // Validar que no se asigne a sí mismo como padre
        if ($validated['padre_id'] == $menuItem->id) {
            return back()->withErrors([
                'padre_id' => 'Un elemento no puede ser padre de sí mismo.'
            ])->withInput();
        }

        // Validar que no se cree dependencia circular
        if ($validated['padre_id'] && $this->creaCircular($menuItem->id, $validated['padre_id'])) {
            return back()->withErrors([
                'padre_id' => 'Esta asignación crearía una dependencia circular.'
            ])->withInput();
        }

        // Validar orden único dentro del mismo nivel (excluyendo el actual)
        $conflictoOrden = MenuItem::where('orden', $validated['orden'])
            ->where('padre_id', $validated['padre_id'])
            ->where('id', '!=', $menuItem->id)
            ->exists();

        if ($conflictoOrden) {
            return back()->withErrors([
                'orden' => 'Ya existe otro elemento con este orden en el mismo nivel.'
            ])->withInput();
        }

        $menuItem->update($validated);

        // Limpiar caché del menú
        $this->limpiarCacheMenu();

        return redirect()->route('responsable.menu-items.index')
            ->with('success', 'Elemento de menú actualizado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(MenuItem $menuItem)
    {
        $menuItem->update(['activo' => !$menuItem->activo]);

        // Limpiar caché del menú
        $this->limpiarCacheMenu();

        $estado = $menuItem->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Elemento de menú {$estado} exitosamente.");
    }

    /**
     * Reordenar elementos del menú
     */
    public function reordenar(Request $request)
    {
        $validated = $request->validate([
            'elementos' => ['required', 'array'],
            'elementos.*.id' => ['required', 'exists:MENU_ITEM,id'],
            'elementos.*.orden' => ['required', 'integer', 'min:1'],
            'elementos.*.padre_id' => ['nullable', 'exists:MENU_ITEM,id'],
        ], [
            'elementos.required' => 'Debe proporcionar elementos para reordenar.',
        ]);

        try {
            foreach ($validated['elementos'] as $elemento) {
                MenuItem::where('id', $elemento['id'])->update([
                    'orden' => $elemento['orden'],
                    'padre_id' => $elemento['padre_id'],
                ]);
            }

            // Limpiar caché del menú
            $this->limpiarCacheMenu();

            return back()->with('success', 'Menú reordenado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error reordenando el menú: ' . $e->getMessage());
        }
    }

    /**
     * Obtener menú por rol para APIs
     */
    public function getMenuPorRol(Request $request)
    {
        $rol = $request->input('rol', 'TODOS');

        $menu = Cache::remember("menu_rol_{$rol}", 3600, function () use ($rol) {
            return MenuItem::getMenuPorRol($rol);
        });

        return response()->json($menu);
    }

    /**
     * Obtener estructura completa del menú
     */
    public function getEstructuraCompleta()
    {
        $estructura = Cache::remember('menu_estructura_completa', 3600, function () {
            return MenuItem::getEstructuraJerarquica();
        });

        return response()->json($estructura);
    }

    /**
     * Duplicar elemento de menú
     */
    public function duplicar(MenuItem $menuItem)
    {
        // Encontrar el siguiente orden disponible
        $maxOrden = MenuItem::where('padre_id', $menuItem->padre_id)->max('orden');

        $nuevo = MenuItem::create([
            'titulo' => $menuItem->titulo . ' (Copia)',
            'ruta' => $menuItem->ruta,
            'icono' => $menuItem->icono,
            'orden' => $maxOrden + 1,
            'padre_id' => $menuItem->padre_id,
            'rol' => $menuItem->rol,
            'activo' => false, // Crear inactivo por seguridad
        ]);

        // Limpiar caché del menú
        $this->limpiarCacheMenu();

        return back()->with('success', "Elemento duplicado exitosamente como '{$nuevo->titulo}'.");
    }

    /**
     * Obtener iconos disponibles
     */
    private function getIconosDisponibles()
    {
        return [
            'home' => 'Inicio',
            'users' => 'Usuarios',
            'user-circle' => 'Perfil',
            'academic-cap' => 'Académico',
            'book-open' => 'Libro',
            'calendar' => 'Calendario',
            'chart-bar' => 'Gráficos',
            'cog' => 'Configuración',
            'settings' => 'Ajustes',
            'clipboard-list' => 'Lista',
            'clipboard-check' => 'Verificado',
            'credit-card' => 'Pagos',
            'document-text' => 'Documento',
            'pencil-alt' => 'Editar',
            'trending-up' => 'Estadísticas',
            'certificate' => 'Certificado',
            'user-group' => 'Grupo',
            'folder' => 'Carpeta',
            'search' => 'Buscar',
            'bell' => 'Notificaciones',
            'mail' => 'Correo',
            'database' => 'Base de Datos',
            'shield-check' => 'Seguridad',
            'key' => 'Acceso',
        ];
    }

    /**
     * Verificar si la asignación crearía una dependencia circular
     */
    private function creaCircular($elementoId, $padrePropuesto)
    {
        $padre = MenuItem::find($padrePropuesto);

        while ($padre) {
            if ($padre->id == $elementoId) {
                return true; // Dependencia circular encontrada
            }
            $padre = $padre->padre;
        }

        return false;
    }

    /**
     * Limpiar caché del menú
     */
    private function limpiarCacheMenu()
    {
        $roles = ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR', 'TODOS'];

        foreach ($roles as $rol) {
            Cache::forget("menu_rol_{$rol}");
        }

        Cache::forget('menu_estructura_completa');
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_elementos' => MenuItem::count(),
            'elementos_activos' => MenuItem::activo()->count(),
            'elementos_principales' => MenuItem::principales()->count(),
            'elementos_submenu' => MenuItem::submenu()->count(),
            'por_rol' => MenuItem::groupBy('rol')
                ->selectRaw('rol, count(*) as total')
                ->pluck('total', 'rol'),
            'profundidad_maxima' => MenuItem::max('orden'),
        ];

        return response()->json($stats);
    }

    /**
     * Exportar menú como JSON
     */
    public function exportar()
    {
        $menu = MenuItem::with(['hijos'])->principales()->orderBy('orden')->get();

        return response()->json($menu)
            ->header('Content-Disposition', 'attachment; filename="menu_cicit.json"');
    }

    /**
     * Obtener rutas disponibles del sistema
     */
    public function getRutasDisponibles()
    {
        // Lista de rutas comunes del sistema CICIT
        $rutas = [
            '/' => 'Inicio',
            '/dashboard' => 'Dashboard',
            '/responsable/usuarios' => 'Gestión de Usuarios',
            '/responsable/gestiones' => 'Gestión de Gestiones',
            '/responsable/cursos' => 'Gestión de Cursos',
            '/responsable/certificados' => 'Gestión de Certificados',
            '/administrativo/participantes' => 'Gestión de Participantes',
            '/administrativo/preinscripciones' => 'Preinscripciones',
            '/administrativo/inscripciones' => 'Inscripciones',
            '/administrativo/pagos' => 'Gestión de Pagos',
            '/tutor/mis-cursos' => 'Mis Cursos',
            '/tutor/tareas' => 'Gestión de Tareas',
            '/tutor/asistencias' => 'Control de Asistencia',
            '/tutor/notas' => 'Registro de Notas',
            '/perfil' => 'Mi Perfil',
            '/configuracion' => 'Configuración Personal',
        ];

        return response()->json($rutas);
    }
}
