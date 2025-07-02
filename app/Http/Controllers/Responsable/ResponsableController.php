<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Preinscripcion;
use App\Models\Pago;
use App\Models\Certificado;
use App\Models\VisitaPagina;
use App\Models\Participante;
use App\Models\TipoParticipante;
use App\Models\Gestion;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ResponsableController extends Controller
{
    /**
     * Dashboard para responsables
     */
    public function dashboard(): Response
    {
        $stats = [
            // Estadísticas generales del sistema
            'total_usuarios' => Usuario::where('activo', true)->count(),
            'total_participantes' => Participante::count(),
            'total_cursos' => Curso::where('activo', true)->count(),
            'cursos_activos' => Curso::where('activo', true)
                ->where('fecha_inicio', '<=', now())
                ->where('fecha_fin', '>=', now())
                ->count(),

            // Inscripciones y preinscripciones
            'total_inscripciones' => Inscripcion::count(),
            'preinscripciones_pendientes' => Preinscripcion::where('estado', 'PENDIENTE')->count(),
            'inscripciones_mes_actual' => Inscripcion::whereMonth('created_at', Carbon::now()->month)->count(),

            // Certificados y pagos
            'certificados_emitidos' => Certificado::count(),
            'ingresos_totales' => Pago::sum('monto'),
            'ingresos_mes_actual' => Pago::whereMonth('created_at', Carbon::now()->month)->sum('monto'),

            // Distribución por tipo de participante
            'participantes_por_tipo' => $this->getParticipantesPorTipo(),

            // Estadísticas de acceso
            'visitas_hoy' => VisitaPagina::whereDate('created_at', Carbon::today())->count(),
            'visitas_mes' => VisitaPagina::whereMonth('created_at', Carbon::now()->month)->count(),
        ];

        // Datos para gestión de cursos
        $cursosData = $this->getCursosData();

        return Inertia::render('Dashboard/ResponsableDashboard', [
            'estadisticas' => $stats,
            'userThemeConfig' => session('user_theme_config', null),
            // Datos para gestión de cursos
            'cursosData' => $cursosData['cursosData'],
            'tutores' => $cursosData['tutores'],
            'gestiones' => $cursosData['gestiones'],
            'tiposParticipante' => $cursosData['tiposParticipante'],
            'cursosEstadisticas' => $cursosData['cursosEstadisticas'],
        ]);
    }

    /**
     * Obtener datos de cursos para el dashboard
     */
    private function getCursosData()
    {
        $cursos = Curso::with(['tutor:id,nombre,apellido', 'gestion:id,nombre', 'precios.tipoParticipante:id,codigo,descripcion'])
            ->withCount(['inscripciones', 'preinscripciones'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Datos adicionales para la gestión de cursos
        $tutores = Usuario::where('rol', 'TUTOR')
            ->where('activo', true)
            ->select('id', 'nombre', 'apellido', 'email')
            ->orderBy('nombre')
            ->get();

        $gestiones = Gestion::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $tiposParticipante = TipoParticipante::where('activo', true)
            ->orderBy('codigo')
            ->get();

        // Estadísticas de cursos
        $totalCursos = Curso::count();
        $cursosActivos = Curso::where('activo', true)->count();
        $cursosEnProgreso = Curso::where('activo', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->count();
        $cursosConInscripciones = Curso::whereHas('inscripciones')->count();

        // 🐛 DEBUG TEMPORAL
        Log::info('DEBUG getCursosData: ' . $cursos->count() . ' cursos encontrados');
        if ($cursos->count() > 0) {
            Log::info('Primer curso: ' . $cursos->first()->nombre);
            Log::info('Tutor del primer curso: ' . ($cursos->first()->tutor ? $cursos->first()->tutor->nombre : 'SIN TUTOR'));
        }
        Log::info('DEBUG getCursosData: ' . $tutores->count() . ' tutores, ' . $gestiones->count() . ' gestiones, ' . $tiposParticipante->count() . ' tipos participante');

        return [
            'cursosData' => [
                'data' => $cursos,
                'links' => [],
                'meta' => [
                    'total' => $totalCursos,
                    'per_page' => 50,
                    'current_page' => 1
                ]
            ],
            'tutores' => $tutores,
            'gestiones' => $gestiones,
            'tiposParticipante' => $tiposParticipante,
            'cursosEstadisticas' => [
                'total' => $totalCursos,
                'activos' => $cursosActivos,
                'en_progreso' => $cursosEnProgreso,
                'con_inscripciones' => $cursosConInscripciones
            ]
        ];
    }

    /**
     * Obtener participantes por tipo para estadísticas
     */
    private function getParticipantesPorTipo()
    {
        return DB::table('PARTICIPANTE')
            ->join('TIPO_PARTICIPANTE', 'PARTICIPANTE.tipo_participante_id', '=', 'TIPO_PARTICIPANTE.id')
            ->select('TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion', DB::raw('count(*) as total'))
            ->where('TIPO_PARTICIPANTE.activo', true)
            ->groupBy('TIPO_PARTICIPANTE.id', 'TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion')
            ->get();
    }

    /**
     * Gestión de usuarios del sistema
     */
    public function usuarios(Request $request): Response
    {
        $query = Usuario::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('registro', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('carnet', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->input('rol'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->input('activo'));
        }

        // Ordenamiento
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $usuarios = $query->paginate(15)->withQueryString();

        return Inertia::render('Responsable/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'],
        ]);
    }

    /**
     * Crear nuevo usuario
     */
    public function storeUsuario(Request $request)
    {
        $validated = $request->validate([
            'registro' => ['required', 'string', 'max:20', 'unique:USUARIO,registro'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'carnet' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:USUARIO,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', 'in:RESPONSABLE,ADMINISTRATIVO,TUTOR'],
            'activo' => ['boolean'],
        ]);

        // Generar contraseña automáticamente
        $tempPassword = 'cicit' . rand(1000, 9999);
        $validated['password'] = Hash::make($tempPassword);

        $usuario = Usuario::create($validated);

        // Si es una petición AJAX/Inertia desde el dashboard, devolver respuesta sin redirect
        if ($request->header('X-Inertia') || $request->ajax()) {
            return back()->with('success', "Usuario creado exitosamente. Contraseña temporal: {$tempPassword}");
        }

        return redirect()->route('responsable.usuarios')
            ->with('success', "Usuario creado exitosamente. Contraseña temporal: {$tempPassword}");
    }

    /**
     * Actualizar usuario existente
     */
    public function updateUsuario(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'registro' => ['required', 'string', 'max:20', Rule::unique('USUARIO', 'registro')->ignore($usuario->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'carnet' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('USUARIO', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', 'in:RESPONSABLE,ADMINISTRATIVO,TUTOR'],
            'activo' => ['boolean'],
        ]);

        $usuario->update($validated);

        // Si es una petición AJAX/Inertia desde el dashboard, devolver respuesta sin redirect
        if ($request->header('X-Inertia') || $request->ajax()) {
            return back()->with('success', 'Usuario actualizado exitosamente');
        }

        return redirect()->route('responsable.usuarios')
            ->with('success', 'Usuario actualizado exitosamente');
    }

    /**
     * Cambiar estado activo/inactivo del usuario
     */
    public function toggleUserStatus(Usuario $usuario, Request $request)
    {
        $usuario->update([
            'activo' => !$usuario->activo
        ]);

        // Si es una petición AJAX/Inertia desde el dashboard, devolver respuesta sin redirect
        if ($request->header('X-Inertia') || $request->ajax()) {
            return back()->with('success', $usuario->activo ? 'Usuario activado' : 'Usuario desactivado');
        }

        return redirect()->route('responsable.usuarios')
            ->with('success', $usuario->activo ? 'Usuario activado' : 'Usuario desactivado');
    }

    /**
     * Restablecer contraseña del usuario
     */
    public function resetUserPassword(Usuario $usuario)
    {
        // Generar nueva contraseña temporal
        $newPassword = 'cicit' . rand(1000, 9999);

        $usuario->update([
            'password' => Hash::make($newPassword)
        ]);

        return redirect()->route('responsable.usuarios')
            ->with('success', "Contraseña restablecida. Nueva contraseña: {$newPassword}");
    }

    /**
     * Gestión de cursos
     */
    public function cursos(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Gestión de Cursos',
            'description' => 'Administrar cursos y contenido educativo',
            'module' => 'Cursos',
        ]);
    }

    /**
     * Estadísticas avanzadas
     */
    public function estadisticas(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Estadísticas Avanzadas',
            'description' => 'Ver reportes detallados del sistema',
            'module' => 'Estadísticas',
        ]);
    }

    /**
     * Configuración del sistema
     */
    public function configuracion(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Configuración del Sistema',
            'description' => 'Ajustes generales y configuración del sistema',
            'module' => 'Configuración',
        ]);
    }

    /**
     * Gestión de pagos
     */
    public function pagos(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Gestión de Pagos',
            'description' => 'Administrar pagos e ingresos del sistema',
            'module' => 'Pagos',
        ]);
    }

    /**
     * Reportes del sistema
     */
    public function reportes(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Reportes del Sistema',
            'description' => 'Generar y descargar reportes detallados',
            'module' => 'Reportes',
        ]);
    }

    /**
     * Gestión de base de datos
     */
    public function database(): Response
    {
        return Inertia::render('Responsable/PlaceholderPage', [
            'title' => 'Gestión de Base de Datos',
            'description' => 'Respaldos y mantenimiento de la base de datos',
            'module' => 'Base de Datos',
        ]);
    }

    /**
     * Obtener usuarios para AJAX
     */
    public function getUsuariosData(Request $request)
    {
        $query = Usuario::query();

        // Filtros
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('registro', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('carnet', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('rol', $request->input('rol'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', (bool) $request->input('activo'));
        }

        // Ordenamiento
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $usuarios = $query->get()->map(function ($usuario) {
            return [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'registro' => $usuario->registro,
                'carnet' => $usuario->carnet,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'rol' => $usuario->rol,
                'activo' => $usuario->activo,
                'ultimo_acceso' => $usuario->ultimo_acceso,
                'created_at' => $usuario->created_at,
            ];
        });

        return response()->json([
            'users' => $usuarios,
            'stats' => [
                'total' => Usuario::count(),
                'activos' => Usuario::where('activo', true)->count(),
                'responsables' => Usuario::where('rol', 'RESPONSABLE')->count(),
                'administrativos' => Usuario::where('rol', 'ADMINISTRATIVO')->count(),
                'tutores' => Usuario::where('rol', 'TUTOR')->count(),
            ]
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function deleteUsuario(Usuario $usuario, Request $request)
    {
        // Prevenir eliminación del último responsable
        if ($usuario->rol === 'RESPONSABLE') {
            $responsablesActivos = Usuario::where('rol', 'RESPONSABLE')
                ->where('activo', true)
                ->where('id', '!=', $usuario->id)
                ->count();

            if ($responsablesActivos === 0) {
                if ($request->header('X-Inertia') || $request->ajax()) {
                    return back()->withErrors(['error' => 'No se puede eliminar el último responsable del sistema']);
                }

                return redirect()->route('responsable.usuarios')
                    ->with('error', 'No se puede eliminar el último responsable del sistema');
            }
        }

        $usuario->delete();

        // Si es una petición AJAX/Inertia desde el dashboard, devolver respuesta sin redirect
        if ($request->header('X-Inertia') || $request->ajax()) {
            return back()->with('success', 'Usuario eliminado exitosamente');
        }

        return redirect()->route('responsable.usuarios')
            ->with('success', 'Usuario eliminado exitosamente');
    }
}
