<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Estadistica;
use App\Models\Usuario;
use App\Models\Participante;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Certificado;
use App\Models\Pago;
use App\Models\VisitaPagina;
use App\Models\BusquedaLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede ver estadísticas completas
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
        // Calcular estadísticas actuales
        Estadistica::calcularEstadisticasActuales();

        $estadisticas = Estadistica::query()
            ->when($request->search, function ($query, $search) {
                $query->where('tipo', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            })
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha', '<=', $fecha);
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('tipo')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Responsable/Estadisticas/Index', [
            'estadisticas' => $estadisticas,
            'filters' => $request->only(['search', 'tipo', 'fecha_desde', 'fecha_hasta']),
            'tipos' => $this->getTiposEstadisticas(),
            'resumenGeneral' => $this->getResumenGeneral(),
        ]);
    }

    /**
     * Dashboard principal de estadísticas
     */
    public function dashboard(): Response
    {
        return Inertia::render('Responsable/Estadisticas/Dashboard', [
            'estadisticasGenerales' => $this->getEstadisticasGenerales(),
            'estadisticasAcademicas' => $this->getEstadisticasAcademicas(),
            'estadisticasFinancieras' => $this->getEstadisticasFinancieras(),
            'estadisticasAcceso' => $this->getEstadisticasAcceso(),
            'evolucionMensual' => $this->getEvolucionMensual(),
            'distribucionParticipantes' => $this->getDistribucionParticipantes(),
            'cursosPopulares' => $this->getCursosPopulares(),
            'rendimientoTutores' => $this->getRendimientoTutores(),
        ]);
    }

    /**
     * Reportes específicos
     */
    public function reportes(Request $request): Response
    {
        $tipoReporte = $request->input('tipo', 'general');
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subMonths(3)->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());

        $datos = match ($tipoReporte) {
            'inscripciones' => $this->getReporteInscripciones($fechaInicio, $fechaFin),
            'financiero' => $this->getReporteFinanciero($fechaInicio, $fechaFin),
            'academico' => $this->getReporteAcademico($fechaInicio, $fechaFin),
            'certificados' => $this->getReporteCertificados($fechaInicio, $fechaFin),
            'acceso' => $this->getReporteAcceso($fechaInicio, $fechaFin),
            default => $this->getReporteGeneral($fechaInicio, $fechaFin),
        };

        return Inertia::render('Responsable/Estadisticas/Reportes', [
            'tipoReporte' => $tipoReporte,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'datos' => $datos,
            'tiposReporte' => [
                'general' => 'Reporte General',
                'inscripciones' => 'Reporte de Inscripciones',
                'financiero' => 'Reporte Financiero',
                'academico' => 'Reporte Académico',
                'certificados' => 'Reporte de Certificados',
                'acceso' => 'Reporte de Acceso al Sistema',
            ]
        ]);
    }

    /**
     * Exportar estadísticas
     */
    public function exportar(Request $request)
    {
        $formato = $request->input('formato', 'json');
        $tipo = $request->input('tipo', 'general');
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->subMonth()->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());

        $datos = $this->getDatosExportacion($tipo, $fechaInicio, $fechaFin);

        switch ($formato) {
            case 'csv':
                return $this->exportarCSV($datos, $tipo);
            case 'excel':
                return $this->exportarExcel($datos, $tipo);
            default:
                return response()->json($datos)
                    ->header('Content-Disposition', "attachment; filename=\"estadisticas_cicit_{$tipo}.json\"");
        }
    }

    /**
     * Estadísticas en tiempo real para APIs
     */
    public function tiempoReal()
    {
        $stats = [
            'usuarios_online' => $this->getUsuariosOnline(),
            'inscripciones_hoy' => Inscripcion::whereDate('created_at', Carbon::today())->count(),
            'pagos_hoy' => Pago::whereDate('fecha_pago', Carbon::today())->sum('monto'),
            'certificados_emitidos_hoy' => Certificado::whereDate('fecha_emision', Carbon::today())->count(),
            'visitas_hoy' => VisitaPagina::hoy()->count(),
            'busquedas_hoy' => BusquedaLog::hoy()->count(),
            'ultimo_pago' => Pago::latest('fecha_pago')->first(),
            'ultima_inscripcion' => Inscripcion::latest('created_at')->with(['participante', 'curso'])->first(),
        ];

        return response()->json($stats);
    }

    /**
     * Comparativas entre períodos
     */
    public function comparativas(Request $request)
    {
        $periodo1_inicio = $request->input('periodo1_inicio', Carbon::now()->subMonths(2)->toDateString());
        $periodo1_fin = $request->input('periodo1_fin', Carbon::now()->subMonth()->toDateString());
        $periodo2_inicio = $request->input('periodo2_inicio', Carbon::now()->subMonth()->toDateString());
        $periodo2_fin = $request->input('periodo2_fin', Carbon::now()->toDateString());

        $comparativa = [
            'periodo1' => [
                'inicio' => $periodo1_inicio,
                'fin' => $periodo1_fin,
                'datos' => $this->getEstadisticasPeriodo($periodo1_inicio, $periodo1_fin),
            ],
            'periodo2' => [
                'inicio' => $periodo2_inicio,
                'fin' => $periodo2_fin,
                'datos' => $this->getEstadisticasPeriodo($periodo2_inicio, $periodo2_fin),
            ],
            'diferencias' => $this->calcularDiferencias(
                $this->getEstadisticasPeriodo($periodo1_inicio, $periodo1_fin),
                $this->getEstadisticasPeriodo($periodo2_inicio, $periodo2_fin)
            ),
        ];

        return response()->json($comparativa);
    }

    /**
     * Recalcular todas las estadísticas
     */
    public function recalcular()
    {
        try {
            Estadistica::calcularEstadisticasActuales();

            return back()->with('success', 'Estadísticas recalculadas exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error recalculando estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas generales del sistema
     */
    private function getEstadisticasGenerales()
    {
        return [
            'total_usuarios' => Usuario::count(),
            'usuarios_activos' => Usuario::activo()->count(),
            'total_participantes' => Participante::count(),
            'participantes_activos' => Participante::activo()->count(),
            'total_cursos' => Curso::count(),
            'cursos_activos' => Curso::activo()->count(),
            'total_inscripciones' => Inscripcion::count(),
            'inscripciones_activas' => Inscripcion::activas()->count(),
            'certificados_emitidos' => Certificado::count(),
            'crecimiento_mensual' => [
                'usuarios' => $this->getCrecimientoMensual(Usuario::class),
                'participantes' => $this->getCrecimientoMensual(Participante::class),
                'inscripciones' => $this->getCrecimientoMensual(Inscripcion::class),
            ]
        ];
    }

    /**
     * Obtener estadísticas académicas
     */
    private function getEstadisticasAcademicas()
    {
        return [
            'cursos_en_progreso' => Curso::enProgreso()->count(),
            'promedio_estudiantes_curso' => round(Curso::activo()->avg('cupos_ocupados'), 1),
            'tasa_aprobacion' => $this->getTasaAprobacion(),
            'promedio_asistencia' => $this->getPromedioAsistenciaGeneral(),
            'certificados_por_tipo' => [
                'participacion' => Certificado::participacion()->count(),
                'aprobacion' => Certificado::aprobacion()->count(),
                'mencion_honor' => Certificado::mencionHonor()->count(),
            ],
            'tutores_mas_activos' => $this->getTutoresMasActivos(),
        ];
    }

    /**
     * Obtener estadísticas financieras
     */
    private function getEstadisticasFinancieras()
    {
        return [
            'ingresos_totales' => Pago::sum('monto'),
            'ingresos_mes_actual' => Pago::whereMonth('fecha_pago', Carbon::now()->month)->sum('monto'),
            'ingresos_mes_anterior' => Pago::whereMonth('fecha_pago', Carbon::now()->subMonth()->month)->sum('monto'),
            'promedio_pago' => round(Pago::avg('monto'), 2),
            'pagos_por_tipo_participante' => $this->getPagosPorTipoParticipante(),
            'evolucion_ingresos' => $this->getEvolucionIngresos(),
        ];
    }

    /**
     * Obtener estadísticas de acceso al sistema
     */
    private function getEstadisticasAcceso()
    {
        return [
            'visitas_totales' => VisitaPagina::count(),
            'visitas_mes' => VisitaPagina::delMesActual()->count(),
            'visitas_hoy' => VisitaPagina::hoy()->count(),
            'paginas_mas_visitadas' => VisitaPagina::getPaginasMasVisitadas(10),
            'busquedas_populares' => BusquedaLog::getTerminosMasBuscados(10),
            'usuarios_activos_mes' => VisitaPagina::delMesActual()
                ->whereNotNull('usuario_id')
                ->distinct('usuario_id')
                ->count(),
        ];
    }

    /**
     * Obtener evolución mensual
     */
    private function getEvolucionMensual()
    {
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = [
                'mes' => $fecha->format('M Y'),
                'inscripciones' => Inscripcion::whereMonth('created_at', $fecha->month)
                    ->whereYear('created_at', $fecha->year)->count(),
                'certificados' => Certificado::whereMonth('fecha_emision', $fecha->month)
                    ->whereYear('fecha_emision', $fecha->year)->count(),
                'ingresos' => Pago::whereMonth('fecha_pago', $fecha->month)
                    ->whereYear('fecha_pago', $fecha->year)->sum('monto'),
            ];
        }
        return $meses;
    }

    /**
     * Obtener distribución de participantes por tipo
     */
    private function getDistribucionParticipantes()
    {
        return Participante::join('TIPO_PARTICIPANTE', 'PARTICIPANTE.tipo_participante_id', '=', 'TIPO_PARTICIPANTE.id')
            ->groupBy('TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion')
            ->select('TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion', DB::raw('count(*) as total'))
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->codigo,
                    'descripcion' => $item->descripcion,
                    'total' => $item->total,
                ];
            });
    }

    /**
     * Obtener cursos más populares
     */
    private function getCursosPopulares()
    {
        return Curso::withCount('inscripciones')
            ->orderByDesc('inscripciones_count')
            ->take(10)
            ->get(['id', 'nombre', 'inscripciones_count'])
            ->map(function ($curso) {
                return [
                    'curso' => $curso->nombre,
                    'inscripciones' => $curso->inscripciones_count,
                ];
            });
    }

    /**
     * Obtener rendimiento de tutores
     */
    private function getRendimientoTutores()
    {
        return Usuario::role('TUTOR')
            ->withCount([
                'cursos',
                'cursos as cursos_activos_count' => function ($query) {
                    $query->where('activo', true);
                }
            ])
            ->with(['cursos' => function ($query) {
                $query->withCount('inscripciones');
            }])
            ->get()
            ->map(function ($tutor) {
                $totalEstudiantes = $tutor->cursos->sum('inscripciones_count');
                return [
                    'tutor' => $tutor->nombre_completo,
                    'cursos_totales' => $tutor->cursos_count,
                    'cursos_activos' => $tutor->cursos_activos_count,
                    'total_estudiantes' => $totalEstudiantes,
                ];
            });
    }

    /**
     * Métodos auxiliares para cálculos específicos
     */
    private function getTasaAprobacion()
    {
        $totalInscripciones = Inscripcion::count();
        $aprobados = Inscripcion::where('estado', 'APROBADO')->count();

        return $totalInscripciones > 0 ? round(($aprobados / $totalInscripciones) * 100, 2) : 0;
    }

    private function getPromedioAsistenciaGeneral()
    {
        // Implementar cálculo de promedio de asistencia general
        return 85.5; // Placeholder
    }

    private function getTutoresMasActivos()
    {
        return Usuario::role('TUTOR')
            ->withCount(['cursos' => function ($query) {
                $query->activo();
            }])
            ->orderByDesc('cursos_count')
            ->take(5)
            ->get(['nombre', 'apellido', 'cursos_count']);
    }

    private function getPagosPorTipoParticipante()
    {
        return DB::table('PAGO')
            ->join('PREINSCRIPCION', 'PAGO.preinscripcion_id', '=', 'PREINSCRIPCION.id')
            ->join('PARTICIPANTE', 'PREINSCRIPCION.participante_id', '=', 'PARTICIPANTE.id')
            ->join('TIPO_PARTICIPANTE', 'PARTICIPANTE.tipo_participante_id', '=', 'TIPO_PARTICIPANTE.id')
            ->groupBy('TIPO_PARTICIPANTE.codigo')
            ->select('TIPO_PARTICIPANTE.codigo', DB::raw('SUM(PAGO.monto) as total'))
            ->get();
    }

    private function getEvolucionIngresos()
    {
        $ingresos = [];
        for ($i = 11; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $ingresos[] = [
                'mes' => $fecha->format('M Y'),
                'monto' => Pago::whereMonth('fecha_pago', $fecha->month)
                    ->whereYear('fecha_pago', $fecha->year)
                    ->sum('monto')
            ];
        }
        return $ingresos;
    }

    private function getCrecimientoMensual($modelo)
    {
        $mesActual = $modelo::whereMonth('created_at', Carbon::now()->month)->count();
        $mesAnterior = $modelo::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();

        if ($mesAnterior == 0) return 100;

        return round((($mesActual - $mesAnterior) / $mesAnterior) * 100, 2);
    }

    private function getTiposEstadisticas()
    {
        return [
            'total_usuarios' => 'Total de Usuarios',
            'total_participantes' => 'Total de Participantes',
            'cursos_activos' => 'Cursos Activos',
            'inscripciones_mes' => 'Inscripciones del Mes',
            'certificados_emitidos' => 'Certificados Emitidos',
            'ingresos_totales' => 'Ingresos Totales',
            'participantes_ficct' => 'Participantes FICCT',
            'participantes_uagrm' => 'Participantes UAGRM',
            'participantes_externos' => 'Participantes Externos',
        ];
    }

    private function getResumenGeneral()
    {
        return Estadistica::getEstadisticasDashboard();
    }

    private function getUsuariosOnline()
    {
        // Usuarios activos en los últimos 15 minutos
        return VisitaPagina::where('fecha_visita', '>=', Carbon::now()->subMinutes(15))
            ->distinct('usuario_id')
            ->whereNotNull('usuario_id')
            ->count();
    }

    private function getEstadisticasPeriodo($inicio, $fin)
    {
        return [
            'inscripciones' => Inscripcion::whereBetween('created_at', [$inicio, $fin])->count(),
            'certificados' => Certificado::whereBetween('fecha_emision', [$inicio, $fin])->count(),
            'ingresos' => Pago::whereBetween('fecha_pago', [$inicio, $fin])->sum('monto'),
            'participantes' => Participante::whereBetween('created_at', [$inicio, $fin])->count(),
        ];
    }

    private function calcularDiferencias($periodo1, $periodo2)
    {
        $diferencias = [];
        foreach ($periodo1 as $key => $valor1) {
            $valor2 = $periodo2[$key] ?? 0;
            $diferencias[$key] = [
                'absoluta' => $valor2 - $valor1,
                'porcentual' => $valor1 > 0 ? round((($valor2 - $valor1) / $valor1) * 100, 2) : 0,
            ];
        }
        return $diferencias;
    }

    private function getDatosExportacion($tipo, $fechaInicio, $fechaFin)
    {
        return match ($tipo) {
            'inscripciones' => $this->getReporteInscripciones($fechaInicio, $fechaFin),
            'financiero' => $this->getReporteFinanciero($fechaInicio, $fechaFin),
            default => $this->getReporteGeneral($fechaInicio, $fechaFin),
        };
    }

    private function getReporteGeneral($fechaInicio, $fechaFin)
    {
        return [
            'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
            'resumen' => $this->getEstadisticasPeriodo($fechaInicio, $fechaFin),
        ];
    }

    private function getReporteInscripciones($fechaInicio, $fechaFin)
    {
        return Inscripcion::with(['participante', 'curso'])
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();
    }

    private function getReporteFinanciero($fechaInicio, $fechaFin)
    {
        return Pago::with(['preinscripcion.participante', 'preinscripcion.curso'])
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->get();
    }

    private function getReporteAcademico($fechaInicio, $fechaFin)
    {
        return [];
    }

    private function getReporteCertificados($fechaInicio, $fechaFin)
    {
        return Certificado::with(['inscripcion.participante', 'inscripcion.curso'])
            ->whereBetween('fecha_emision', [$fechaInicio, $fechaFin])
            ->get();
    }

    private function getReporteAcceso($fechaInicio, $fechaFin)
    {
        return VisitaPagina::whereBetween('fecha_visita', [$fechaInicio, $fechaFin])
            ->get();
    }

    private function exportarCSV($datos, $tipo)
    {
        // Implementar exportación CSV
        return response('CSV no implementado aún', 501);
    }

    private function exportarExcel($datos, $tipo)
    {
        // Implementar exportación Excel
        return response('Excel no implementado aún', 501);
    }
}
