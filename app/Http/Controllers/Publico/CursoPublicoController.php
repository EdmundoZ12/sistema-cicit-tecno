<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\TipoParticipante;
use App\Models\Gestion;
use App\Models\VisitaPagina;
use App\Models\BusquedaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class CursoPublicoController extends Controller
{
    /**
     * Catálogo público de cursos
     */
    public function index(Request $request): Response
    {
        // Registrar visita a la página de cursos
        VisitaPagina::registrarVisita('/cursos', $request);

        $cursos = Curso::with(['tutor', 'gestion', 'precios.tipoParticipante'])
            ->activo()
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('nivel', 'ILIKE', "%{$search}%")
                    ->orWhereHas('tutor', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%")
                            ->orWhere('apellido', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->nivel, function ($query, $nivel) {
                $query->where('nivel', $nivel);
            })
            ->when($request->gestion_id, function ($query, $gestionId) {
                $query->where('gestion_id', $gestionId);
            })
            ->when($request->modalidad, function ($query, $modalidad) {
                // Filtro por modalidad según el aula (presencial) o virtual
                if ($modalidad === 'presencial') {
                    $query->whereNotNull('aula');
                } elseif ($modalidad === 'virtual') {
                    $query->whereNull('aula');
                }
            })
            ->when($request->estado, function ($query, $estado) {
                $hoy = Carbon::now();
                switch ($estado) {
                    case 'proximamente':
                        $query->where('fecha_inicio', '>', $hoy);
                        break;
                    case 'inscripciones_abiertas':
                        $query->where('fecha_inicio', '>', $hoy)
                            ->where('cupos_ocupados', '<', 'cupos_totales');
                        break;
                    case 'en_curso':
                        $query->where('fecha_inicio', '<=', $hoy)
                            ->where('fecha_fin', '>=', $hoy);
                        break;
                }
            })
            ->when($request->precio_max, function ($query, $precioMax) {
                $query->whereHas('precios', function ($q) use ($precioMax) {
                    $q->where('precio', '<=', $precioMax);
                });
            })
            ->orderBy('fecha_inicio')
            ->paginate(12)
            ->withQueryString();

        // Formatear cursos para el frontend
        $cursosFormateados = $cursos->getCollection()->map(function ($curso) {
            return $this->formatearCursoPublico($curso);
        });
        $cursos->setCollection($cursosFormateados);

        // Opciones para filtros
        $filtros = [
            'niveles' => Curso::activo()->distinct()->pluck('nivel')->filter()->sort()->values(),
            'gestiones' => Gestion::activo()->get(['id', 'nombre']),
            'precio_rango' => $this->getRangoPreciosCursos(),
            'modalidades' => [
                'presencial' => 'Presencial',
                'virtual' => 'Virtual',
                'semipresencial' => 'Semipresencial'
            ]
        ];

        // Estadísticas del catálogo
        $estadisticas = [
            'total_cursos' => Curso::activo()->count(),
            'cursos_disponibles' => Curso::activo()
                ->where('fecha_inicio', '>', Carbon::now())
                ->where('cupos_ocupados', '<', 'cupos_totales')
                ->count(),
            'areas_disponibles' => Curso::activo()->distinct('nivel')->count('nivel'),
            'proximos_inicios' => Curso::activo()
                ->where('fecha_inicio', '>', Carbon::now())
                ->orderBy('fecha_inicio')
                ->take(3)
                ->pluck('fecha_inicio')
                ->map(fn($fecha) => $fecha->format('d/m/Y'))
        ];

        return Inertia::render('Publico/Cursos/Index', [
            'cursos' => $cursos,
            'filtros' => $filtros,
            'estadisticas' => $estadisticas,
            'tiposParticipante' => TipoParticipante::activo()->get(['codigo', 'descripcion']),
            'filtrosActuales' => $request->only(['search', 'nivel', 'gestion_id', 'modalidad', 'estado', 'precio_max']),
            'contadorVisitas' => VisitaPagina::getTextoContador('/cursos'),
        ]);
    }

    /**
     * Mostrar curso específico
     */
    public function show(Curso $curso, Request $request): Response
    {
        // Verificar que el curso esté activo
        if (!$curso->activo) {
            abort(404, 'Curso no disponible');
        }

        // Registrar visita al curso específico
        VisitaPagina::registrarVisita("/cursos/{$curso->id}", $request);

        // Cargar relaciones necesarias
        $curso->load([
            'tutor',
            'gestion',
            'precios.tipoParticipante'
        ]);

        // Formatear curso para vista detallada
        $cursoDetallado = $this->formatearCursoDetallado($curso);

        // Cursos relacionados (mismo nivel o mismo tutor)
        $cursosRelacionados = Curso::activo()
            ->where('id', '!=', $curso->id)
            ->where(function ($query) use ($curso) {
                $query->where('nivel', $curso->nivel)
                    ->orWhere('tutor_id', $curso->tutor_id);
            })
            ->with(['tutor', 'precios.tipoParticipante'])
            ->take(4)
            ->get()
            ->map(function ($cursoRel) {
                return $this->formatearCursoPublico($cursoRel);
            });

        // Verificar disponibilidad para preinscripción
        $disponibleParaPreinscripcion = $this->verificarDisponibilidadPreinscripcion($curso);

        return Inertia::render('Publico/Cursos/Show', [
            'curso' => $cursoDetallado,
            'cursosRelacionados' => $cursosRelacionados,
            'disponibleParaPreinscripcion' => $disponibleParaPreinscripcion,
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'contadorVisitas' => VisitaPagina::getTextoContador("/cursos/{$curso->id}"),
        ]);
    }

    /**
     * Búsqueda específica de cursos
     */
    public function buscar(Request $request)
    {
        $termino = $request->input('q', '');

        if (empty($termino)) {
            return response()->json([
                'termino' => '',
                'total_resultados' => 0,
                'cursos' => [],
            ]);
        }

        $cursos = Curso::activo()
            ->with(['tutor', 'precios.tipoParticipante'])
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                    ->orWhere('nivel', 'ILIKE', "%{$termino}%")
                    ->orWhereHas('tutor', function ($q) use ($termino) {
                        $q->where('nombre', 'ILIKE', "%{$termino}%")
                            ->orWhere('apellido', 'ILIKE', "%{$termino}%");
                    });
            })
            ->orderBy('fecha_inicio')
            ->take(20)
            ->get()
            ->map(function ($curso) {
                return $this->formatearCursoPublico($curso);
            });

        // Registrar búsqueda
        BusquedaLog::registrarBusqueda($termino, $cursos->count(), $request);

        return response()->json([
            'termino' => $termino,
            'total_resultados' => $cursos->count(),
            'cursos' => $cursos,
        ]);
    }

    /**
     * Obtener cursos por categoría/nivel
     */
    public function porCategoria(Request $request, $categoria)
    {
        VisitaPagina::registrarVisita("/cursos/categoria/{$categoria}", $request);

        $cursos = Curso::activo()
            ->where('nivel', $categoria)
            ->with(['tutor', 'gestion', 'precios.tipoParticipante'])
            ->orderBy('fecha_inicio')
            ->paginate(12)
            ->withQueryString();

        $cursosFormateados = $cursos->getCollection()->map(function ($curso) {
            return $this->formatearCursoPublico($curso);
        });
        $cursos->setCollection($cursosFormateados);

        return Inertia::render('Publico/Cursos/Categoria', [
            'categoria' => $categoria,
            'cursos' => $cursos,
            'estadisticas' => [
                'total_en_categoria' => Curso::activo()->where('nivel', $categoria)->count(),
                'disponibles' => Curso::activo()
                    ->where('nivel', $categoria)
                    ->where('fecha_inicio', '>', Carbon::now())
                    ->count(),
            ],
            'contadorVisitas' => VisitaPagina::getTextoContador("/cursos/categoria/{$categoria}"),
        ]);
    }

    /**
     * API para obtener información resumida de un curso
     */
    public function informacionResumida(Curso $curso)
    {
        if (!$curso->activo) {
            return response()->json(['error' => 'Curso no disponible'], 404);
        }

        $curso->load(['tutor', 'precios.tipoParticipante']);

        return response()->json([
            'id' => $curso->id,
            'nombre' => $curso->nombre,
            'descripcion' => substr($curso->descripcion, 0, 200) . '...',
            'tutor' => $curso->tutor->nombre_completo,
            'duracion_horas' => $curso->duracion_horas,
            'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
            'cupos_disponibles' => $curso->cupos_disponibles,
            'precios' => $curso->precios->map(function ($precio) {
                return [
                    'tipo' => $precio->tipoParticipante->codigo,
                    'precio' => $precio->precio_formateado,
                ];
            }),
            'puede_preinscribirse' => $this->verificarDisponibilidadPreinscripcion($curso),
        ]);
    }

    /**
     * Obtener cursos próximos a iniciar
     */
    public function proximosInicios()
    {
        $cursos = Curso::activo()
            ->where('fecha_inicio', '>', Carbon::now())
            ->where('fecha_inicio', '<=', Carbon::now()->addMonths(2))
            ->with(['tutor', 'precios.tipoParticipante'])
            ->orderBy('fecha_inicio')
            ->take(10)
            ->get()
            ->map(function ($curso) {
                return $this->formatearCursoPublico($curso);
            });

        return response()->json($cursos);
    }

    /**
     * Estadísticas públicas de cursos
     */
    public function estadisticasPublicas()
    {
        $stats = [
            'total_cursos_activos' => Curso::activo()->count(),
            'cursos_disponibles' => Curso::activo()
                ->where('fecha_inicio', '>', Carbon::now())
                ->where('cupos_ocupados', '<', 'cupos_totales')
                ->count(),
            'niveles_disponibles' => Curso::activo()->distinct('nivel')->count('nivel'),
            'proximos_30_dias' => Curso::activo()
                ->where('fecha_inicio', '>', Carbon::now())
                ->where('fecha_inicio', '<=', Carbon::now()->addDays(30))
                ->count(),
            'modalidades' => [
                'presencial' => Curso::activo()->whereNotNull('aula')->count(),
                'virtual' => Curso::activo()->whereNull('aula')->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Formatear curso para vista pública
     */
    private function formatearCursoPublico($curso)
    {
        return [
            'id' => $curso->id,
            'nombre' => $curso->nombre,
            'descripcion' => $curso->descripcion,
            'duracion_horas' => $curso->duracion_horas,
            'nivel' => $curso->nivel,
            'logo_url' => $curso->logo_url,
            'tutor' => $curso->tutor->nombre_completo,
            'gestion' => $curso->gestion->nombre,
            'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
            'fecha_fin' => $curso->fecha_fin->format('d/m/Y'),
            'aula' => $curso->aula,
            'modalidad' => $curso->aula ? 'Presencial' : 'Virtual',
            'cupos_totales' => $curso->cupos_totales,
            'cupos_ocupados' => $curso->cupos_ocupados,
            'cupos_disponibles' => $curso->cupos_disponibles,
            'porcentaje_ocupacion' => $curso->porcentaje_ocupacion,
            'estado_inscripcion' => $this->getEstadoInscripcion($curso),
            'precios' => $curso->precios->map(function ($precio) {
                return [
                    'tipo_id' => $precio->tipo_participante_id,
                    'tipo_codigo' => $precio->tipoParticipante->codigo,
                    'tipo_descripcion' => $precio->tipoParticipante->descripcion,
                    'precio' => $precio->precio,
                    'precio_formateado' => $precio->precio_formateado,
                ];
            }),
            'precio_desde' => $curso->precios->min('precio'),
            'precio_hasta' => $curso->precios->max('precio'),
        ];
    }

    /**
     * Formatear curso para vista detallada
     */
    private function formatearCursoDetallado($curso)
    {
        $cursoPublico = $this->formatearCursoPublico($curso);

        // Agregar información adicional para vista detallada
        $cursoPublico['descripcion_completa'] = $curso->descripcion;
        $cursoPublico['periodo_completo'] = $curso->periodo_curso;
        $cursoPublico['duracion_semanas'] = $curso->fecha_inicio->diffInWeeks($curso->fecha_fin);
        $cursoPublico['tutor_info'] = [
            'nombre' => $curso->tutor->nombre_completo,
            'email' => $curso->tutor->email, // Solo para contacto académico
        ];

        return $cursoPublico;
    }

    /**
     * Obtener estado de inscripción del curso
     */
    private function getEstadoInscripcion($curso)
    {
        $hoy = Carbon::now();

        if ($curso->fecha_inicio <= $hoy && $curso->fecha_fin >= $hoy) {
            return 'en_curso';
        }

        if ($curso->fecha_fin < $hoy) {
            return 'finalizado';
        }

        if ($curso->cupos_disponibles <= 0) {
            return 'sin_cupos';
        }

        if ($curso->fecha_inicio > $hoy) {
            return 'disponible';
        }

        return 'no_disponible';
    }

    /**
     * Verificar si se puede preinscribir al curso
     */
    private function verificarDisponibilidadPreinscripcion($curso)
    {
        return [
            'disponible' => $curso->cupos_disponibles > 0 &&
                $curso->fecha_inicio > Carbon::now(),
            'motivo' => $this->getMotivoNoDisponible($curso),
            'cupos_disponibles' => $curso->cupos_disponibles,
            'fecha_limite' => $curso->fecha_inicio->subDays(3)->format('d/m/Y'), // 3 días antes
        ];
    }

    /**
     * Obtener motivo de no disponibilidad
     */
    private function getMotivoNoDisponible($curso)
    {
        if ($curso->cupos_disponibles <= 0) {
            return 'Sin cupos disponibles';
        }

        if ($curso->fecha_inicio <= Carbon::now()) {
            return 'Curso ya iniciado';
        }

        return null;
    }

    /**
     * Obtener rango de precios de cursos
     */
    private function getRangoPreciosCursos()
    {
        $precios = \App\Models\PrecioCurso::whereHas('curso', function ($query) {
            $query->activo();
        })->pluck('precio');

        return [
            'minimo' => $precios->min() ?? 0,
            'maximo' => $precios->max() ?? 1000,
        ];
    }
}
