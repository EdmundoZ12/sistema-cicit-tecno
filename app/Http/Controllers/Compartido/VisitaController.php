<?php

namespace App\Http\Controllers\Compartido;

use App\Http\Controllers\Controller;
use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitaController extends Controller
{
    /**
     * Registrar una nueva visita a una página
     */
    public function registrarVisita(Request $request)
    {
        $request->validate([
            'pagina' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255'
        ]);

        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $userId = Auth::id();

        // Verificar si ya existe una visita reciente del mismo IP en los últimos 5 minutos
        $visitaReciente = VisitaPagina::where('ip_address', $ipAddress)
            ->where('pagina', $request->pagina)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->exists();

        if (!$visitaReciente) {
            VisitaPagina::create([
                'pagina' => $request->pagina,
                'titulo_pagina' => $request->titulo ?? 'Sin título',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'usuario_id' => $userId,
                'tiempo_permanencia' => 0,
                'referrer' => $request->server('HTTP_REFERER')
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Visita registrada correctamente'
        ]);
    }

    /**
     * Obtener contador de visitas para una página específica
     */
    public function contadorPagina(Request $request)
    {
        $pagina = $request->get('pagina', '/');

        $stats = [
            'total_visitas' => VisitaPagina::where('pagina', $pagina)->count(),
            'visitas_hoy' => VisitaPagina::where('pagina', $pagina)
                ->whereDate('created_at', Carbon::today())->count(),
            'visitas_mes' => VisitaPagina::where('pagina', $pagina)
                ->whereMonth('created_at', Carbon::now()->month)->count(),
            'visitantes_unicos' => VisitaPagina::where('pagina', $pagina)
                ->distinct('ip_address')->count(),
        ];

        // Si es una petición AJAX o desde la API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($stats);
        }

        // Si es una petición web normal, redirigir al dashboard
        return redirect()->route('dashboard')->with('visitStats', $stats);
    }

    /**
     * Obtener estadísticas generales del sitio
     */
    public function estadisticasGenerales(Request $request)
    {
        $stats = [
            'total_visitas_sitio' => VisitaPagina::count(),
            'visitas_hoy' => VisitaPagina::whereDate('created_at', Carbon::today())->count(),
            'visitas_mes' => VisitaPagina::whereMonth('created_at', Carbon::now()->month)->count(),
            'visitantes_unicos_mes' => VisitaPagina::whereMonth('created_at', Carbon::now()->month)
                ->distinct('ip_address')->count(),
            'paginas_mas_visitadas' => $this->getPaginasMasVisitadas(),
        ];

        // Si es una petición AJAX o desde la API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($stats);
        }

        // Si es una petición web normal, redirigir al dashboard
        return redirect()->route('dashboard')->with('generalStats', $stats);
    }

    /**
     * Obtener páginas más visitadas
     */
    public function paginasMasVisitadas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes'); // hoy, semana, mes, todo

        $query = VisitaPagina::select('pagina', DB::raw('COUNT(*) as total_visitas'))
            ->groupBy('pagina');

        switch ($periodo) {
            case 'hoy':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'semana':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'mes':
                $query->whereMonth('created_at', Carbon::now()->month);
                break;
            // 'todo' no aplica filtro adicional
        }

        $paginas = $query->orderBy('total_visitas', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'pagina' => $item->pagina,
                    'nombre_amigable' => $this->getNombreAmigablePagina($item->pagina),
                    'total_visitas' => $item->total_visitas,
                ];
            });

        // Si es una petición AJAX o desde la API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($paginas);
        }

        // Si es una petición web normal, redirigir al dashboard
        return redirect()->route('dashboard')->with('paginasVisitadas', $paginas->toArray());
    }

    /**
     * Obtener visitantes en tiempo real (últimos 30 minutos)
     */
    public function visitantesEnLinea(Request $request)
    {
        $visitantesActivos = VisitaPagina::where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->distinct('session_id')
            ->count();

        $ultimasVisitas = VisitaPagina::with('usuario')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function ($visita) {
                return [
                    'pagina' => $this->getNombreAmigablePagina($visita->pagina),
                    'usuario' => $visita->usuario ? $visita->usuario->nombre_completo : 'Visitante anónimo',
                    'ip' => $this->enmascararIP($visita->ip_address),
                    'tiempo' => $visita->created_at->diffForHumans(),
                ];
            });

        $data = [
            'visitantes_activos' => $visitantesActivos,
            'ultimas_visitas' => $ultimasVisitas,
        ];

        // Si es una petición AJAX o desde la API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($data);
        }

        // Si es una petición web normal, redirigir al dashboard
        return redirect()->route('dashboard')->with('visitantesEnLinea', $data);
    }

    /**
     * Obtener datos para el pie de página (contador público)
     */
    public function datosPiePagina(Request $request)
    {
        $paginaActual = $request->get('pagina', '/');

        $datos = [
            'visitas_esta_pagina' => VisitaPagina::where('pagina', $paginaActual)->count(),
            'visitas_hoy_sitio' => VisitaPagina::whereDate('created_at', Carbon::today())->count(),
            'total_visitas_sitio' => VisitaPagina::count(),
            'visitantes_en_linea' => VisitaPagina::where('created_at', '>=', Carbon::now()->subMinutes(30))
                ->distinct('session_id')->count(),
            'fecha_actualizacion' => Carbon::now()->format('d/m/Y H:i'),
        ];

        // Si es una petición AJAX o desde la API, devolver JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($datos);
        }

        // Si es una petición web normal, redirigir al dashboard
        return redirect()->route('dashboard')->with('datosPiePagina', $datos);
    }

    /**
     * Reportes detallados de visitas (solo para RESPONSABLE)
     */
    public function reportes(Request $request)
    {
        // Verificar permisos
        if (!Auth::check() || Auth::user()->rol !== 'RESPONSABLE') {
            abort(403, 'No autorizado');
        }

        $tipo = $request->get('tipo', 'general');
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->subDays(30));
        $fechaFin = $request->get('fecha_fin', Carbon::now());

        switch ($tipo) {
            case 'por_dia':
                return $this->reportePorDia($fechaInicio, $fechaFin);
            case 'por_hora':
                return $this->reportePorHora($fechaInicio, $fechaFin);
            case 'por_pagina':
                return $this->reportePorPagina($fechaInicio, $fechaFin);
            case 'por_usuario':
                return $this->reportePorUsuario($fechaInicio, $fechaFin);
            default:
                return $this->reporteGeneral($fechaInicio, $fechaFin);
        }
    }

    /**
     * Obtener páginas más visitadas (privado)
     */
    private function getPaginasMasVisitadas()
    {
        return VisitaPagina::select('pagina', DB::raw('COUNT(*) as total'))
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('pagina')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'pagina' => $this->getNombreAmigablePagina($item->pagina),
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Obtener nombre amigable para una página
     */
    private function getNombreAmigablePagina($pagina)
    {
        $nombres = [
            '/' => 'Página Principal',
            '/dashboard' => 'Dashboard',
            '/cursos' => 'Catálogo de Cursos',
            '/login' => 'Iniciar Sesión',
            '/register' => 'Registro',
            '/preinscripcion' => 'Preinscripción',
            '/certificados/verificar' => 'Verificar Certificados',
            '/responsable/usuarios' => 'Gestión de Usuarios',
            '/responsable/cursos' => 'Gestión de Cursos',
            '/responsable/estadisticas' => 'Estadísticas',
            '/administrativo/preinscripciones' => 'Preinscripciones',
            '/administrativo/inscripciones' => 'Inscripciones',
            '/administrativo/pagos' => 'Gestión de Pagos',
            '/tutor/mis-cursos' => 'Mis Cursos',
            '/tutor/notas' => 'Gestión de Notas',
            '/tutor/asistencias' => 'Control de Asistencia',
        ];

        // Si no encuentra un nombre específico, generar uno basado en la URL
        if (isset($nombres[$pagina])) {
            return $nombres[$pagina];
        }

        // Intentar generar nombre amigable
        $partes = explode('/', trim($pagina, '/'));
        if (count($partes) >= 2) {
            $seccion = ucfirst($partes[0]);
            $accion = ucfirst(str_replace('-', ' ', $partes[1]));
            return "{$seccion} - {$accion}";
        }

        return ucfirst(str_replace(['/', '-'], [' ', ' '], trim($pagina, '/'))) ?: 'Página Principal';
    }

    /**
     * Enmascarar IP para privacidad
     */
    private function enmascararIP($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $partes = explode('.', $ip);
            return $partes[0] . '.' . $partes[1] . '.xxx.xxx';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $partes = explode(':', $ip);
            return implode(':', array_slice($partes, 0, 3)) . ':xxxx:xxxx:xxxx:xxxx';
        }

        return 'xxx.xxx.xxx.xxx';
    }

    /**
     * Reporte por día
     */
    private function reportePorDia($fechaInicio, $fechaFin)
    {
        $datos = VisitaPagina::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json($datos);
    }

    /**
     * Reporte por hora
     */
    private function reportePorHora($fechaInicio, $fechaFin)
    {
        $datos = VisitaPagina::selectRaw('EXTRACT(HOUR FROM created_at) as hora, COUNT(*) as total')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        return response()->json($datos);
    }

    /**
     * Reporte por página
     */
    private function reportePorPagina($fechaInicio, $fechaFin)
    {
        $datos = VisitaPagina::select('pagina', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->groupBy('pagina')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'pagina' => $this->getNombreAmigablePagina($item->pagina),
                    'url' => $item->pagina,
                    'total' => $item->total,
                ];
            });

        return response()->json($datos);
    }

    /**
     * Reporte por usuario
     */
    private function reportePorUsuario($fechaInicio, $fechaFin)
    {
        $datos = VisitaPagina::with('usuario')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->whereNotNull('usuario_id')
            ->select('usuario_id', DB::raw('COUNT(*) as total'))
            ->groupBy('usuario_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'usuario' => $item->usuario ? $item->usuario->nombre_completo : 'Usuario eliminado',
                    'rol' => $item->usuario ? $item->usuario->rol : 'N/A',
                    'total_visitas' => $item->total,
                ];
            });

        return response()->json($datos);
    }

    /**
     * Reporte general
     */
    private function reporteGeneral($fechaInicio, $fechaFin)
    {
        $stats = [
            'total_visitas' => VisitaPagina::whereBetween('created_at', [$fechaInicio, $fechaFin])->count(),
            'visitantes_unicos' => VisitaPagina::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->distinct('ip_address')->count(),
            'usuarios_registrados' => VisitaPagina::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereNotNull('usuario_id')->distinct('usuario_id')->count(),
            'visitantes_anonimos' => VisitaPagina::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereNull('usuario_id')->distinct('session_id')->count(),
            'promedio_diario' => VisitaPagina::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->selectRaw('COUNT(*) / COUNT(DISTINCT DATE(created_at)) as promedio')
                ->value('promedio'),
        ];

        return response()->json($stats);
    }
}
