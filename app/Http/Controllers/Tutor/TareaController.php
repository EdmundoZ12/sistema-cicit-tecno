<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\NotaTarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class TareaController extends Controller
{
    /**
     * Constructor - Solo TUTOR puede gestionar tareas
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:TUTOR']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $tutor = Auth::user();

        $tareas = Tarea::with(['curso.gestion', 'notas.inscripcion.participante'])
            ->whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->when($request->search, function ($query, $search) {
                $query->where('titulo', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('curso', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_asignacion', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_asignacion', '<=', $fecha);
            })
            ->when($request->estado_calificacion, function ($query, $estado) {
                switch ($estado) {
                    case 'sin_calificar':
                        $query->whereHas('curso.inscripciones', function ($q) {
                            $q->where('estado', '!=', 'RETIRADO')
                                ->whereDoesntHave('notasTareas', function ($subQ) {
                                    $subQ->whereColumn('tarea_id', 'TAREA.id');
                                });
                        });
                        break;
                    case 'parcialmente_calificada':
                        $query->whereHas('notas')
                            ->whereHas('curso.inscripciones', function ($q) {
                                $q->where('estado', '!=', 'RETIRADO')
                                    ->whereDoesntHave('notasTareas', function ($subQ) {
                                        $subQ->whereColumn('tarea_id', 'TAREA.id');
                                    });
                            });
                        break;
                    case 'completamente_calificada':
                        $query->whereDoesntHave('curso.inscripciones', function ($q) {
                            $q->where('estado', '!=', 'RETIRADO')
                                ->whereDoesntHave('notasTareas', function ($subQ) {
                                    $subQ->whereColumn('tarea_id', 'TAREA.id');
                                });
                        });
                        break;
                }
            })
            ->withCount('notas')
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Enriquecer tareas con estadísticas
        $tareas->getCollection()->transform(function ($tarea) {
            return $this->enriquecerTareaConEstadisticas($tarea);
        });

        return Inertia::render('Tutor/Tareas/Index', [
            'tareas' => $tareas,
            'filters' => $request->only([
                'search',
                'curso_id',
                'fecha_desde',
                'fecha_hasta',
                'estado_calificacion'
            ]),
            'cursos' => $tutor->cursos()->activo()->get(['id', 'nombre']),
            'estadisticas' => $this->getEstadisticasTareas($tutor),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $tutor = Auth::user();

        // Obtener cursos activos del tutor
        $cursosActivos = $tutor->cursos()
            ->activo()
            ->where('fecha_fin', '>=', Carbon::now())
            ->get(['id', 'nombre', 'fecha_inicio', 'fecha_fin']);

        return Inertia::render('Tutor/Tareas/Create', [
            'cursos' => $cursosActivos,
            'curso_seleccionado' => $request->curso_id ?
                $cursosActivos->find($request->curso_id) : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => ['required', 'exists:CURSO,id'],
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha_asignacion' => ['required', 'date'],
        ], [
            'curso_id.required' => 'Debe seleccionar un curso.',
            'curso_id.exists' => 'El curso seleccionado no es válido.',
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 100 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'fecha_asignacion.required' => 'La fecha de asignación es obligatoria.',
            'fecha_asignacion.date' => 'La fecha de asignación debe ser válida.',
        ]);

        // Verificar que el curso pertenece al tutor
        $curso = Curso::find($validated['curso_id']);
        if ($curso->tutor_id !== Auth::id()) {
            return back()->withErrors([
                'curso_id' => 'No tienes permiso para crear tareas en este curso.'
            ])->withInput();
        }

        // Verificar que el curso esté activo
        if (!$curso->activo) {
            return back()->withErrors([
                'curso_id' => 'No se pueden crear tareas en un curso inactivo.'
            ])->withInput();
        }

        $tarea = Tarea::create($validated);

        return redirect()->route('tutor.tareas.show', $tarea)
            ->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tarea $tarea): Response
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a esta tarea.');
        }

        $tarea->load([
            'curso.gestion',
            'notas.inscripcion.participante.tipoParticipante'
        ]);

        // Obtener estudiantes del curso y sus calificaciones
        $estudiantes = $tarea->curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) use ($tarea) {
                $nota = $tarea->notas->where('inscripcion_id', $inscripcion->id)->first();

                return [
                    'inscripcion_id' => $inscripcion->id,
                    'participante' => [
                        'nombre' => $inscripcion->participante->nombre_completo,
                        'carnet' => $inscripcion->participante->carnet,
                        'email' => $inscripcion->participante->email,
                        'tipo' => $inscripcion->participante->tipoParticipante->codigo,
                    ],
                    'nota' => $nota ? [
                        'id' => $nota->id,
                        'valor' => $nota->nota,
                        'calificacion' => $nota->calificacion_literal,
                        'color' => $nota->color_nota,
                        'fecha_calificacion' => $nota->created_at->format('d/m/Y H:i'),
                    ] : null,
                    'entregado' => $nota !== null,
                ];
            });

        // Estadísticas de la tarea
        $estadisticas = [
            'total_estudiantes' => $estudiantes->count(),
            'entregadas' => $estudiantes->where('entregado', true)->count(),
            'pendientes' => $estudiantes->where('entregado', false)->count(),
            'porcentaje_entrega' => $tarea->porcentajeEntrega(),
            'promedio_calificacion' => round($tarea->promedioCalificaciones(), 2),
            'nota_maxima' => $tarea->notas->max('nota') ?? 0,
            'nota_minima' => $tarea->notas->min('nota') ?? 0,
            'aprobados' => $tarea->notas->where('nota', '>=', 51)->count(),
            'reprobados' => $tarea->notas->where('nota', '<', 51)->count(),
        ];

        // Distribución de calificaciones
        $distribucionNotas = [
            'excelente' => $tarea->notas->where('nota', '>=', 90)->count(),
            'muy_bueno' => $tarea->notas->whereBetween('nota', [80, 89])->count(),
            'bueno' => $tarea->notas->whereBetween('nota', [70, 79])->count(),
            'regular' => $tarea->notas->whereBetween('nota', [51, 69])->count(),
            'insuficiente' => $tarea->notas->where('nota', '<', 51)->count(),
        ];

        return Inertia::render('Tutor/Tareas/Show', [
            'tarea' => $tarea,
            'estudiantes' => $estudiantes,
            'estadisticas' => $estadisticas,
            'distribucion_notas' => $distribucionNotas,
            'puede_editar' => $this->puedeEditarTarea($tarea),
            'puede_eliminar' => $this->puedeEliminarTarea($tarea),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tarea $tarea): Response
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta tarea.');
        }

        if (!$this->puedeEditarTarea($tarea)) {
            return redirect()->route('tutor.tareas.show', $tarea)
                ->with('error', 'No se puede editar una tarea que ya tiene calificaciones.');
        }

        $tutor = Auth::user();

        return Inertia::render('Tutor/Tareas/Edit', [
            'tarea' => $tarea->load('curso'),
            'cursos' => $tutor->cursos()->activo()->get(['id', 'nombre']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para editar esta tarea.');
        }

        if (!$this->puedeEditarTarea($tarea)) {
            return back()->with('error', 'No se puede editar una tarea que ya tiene calificaciones.');
        }

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha_asignacion' => ['required', 'date'],
        ], [
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.max' => 'El título no puede tener más de 100 caracteres.',
            'descripcion.max' => 'La descripción no puede tener más de 1000 caracteres.',
            'fecha_asignacion.required' => 'La fecha de asignación es obligatoria.',
        ]);

        $tarea->update($validated);

        return redirect()->route('tutor.tareas.show', $tarea)
            ->with('success', 'Tarea actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para eliminar esta tarea.');
        }

        if (!$this->puedeEliminarTarea($tarea)) {
            return back()->with('error', 'No se puede eliminar una tarea que ya tiene calificaciones.');
        }

        $nombreTarea = $tarea->titulo;
        $tarea->delete();

        return redirect()->route('tutor.tareas.index')
            ->with('success', "Tarea '{$nombreTarea}' eliminada exitosamente.");
    }

    /**
     * Calificar múltiples estudiantes a la vez
     */
    public function calificarMasivo(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'calificaciones' => ['required', 'array', 'min:1'],
            'calificaciones.*.inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'calificaciones.*.nota' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'calificaciones.required' => 'Debe proporcionar calificaciones.',
            'calificaciones.*.nota.required' => 'La nota es obligatoria.',
            'calificaciones.*.nota.numeric' => 'La nota debe ser un número.',
            'calificaciones.*.nota.min' => 'La nota no puede ser menor a 0.',
            'calificaciones.*.nota.max' => 'La nota no puede ser mayor a 100.',
        ]);

        $calificadas = 0;
        $errores = [];

        foreach ($validated['calificaciones'] as $calificacion) {
            try {
                // Verificar que la inscripción pertenece al curso de la tarea
                $inscripcion = Inscripcion::find($calificacion['inscripcion_id']);

                if ($inscripcion->curso_id !== $tarea->curso_id) {
                    $errores[] = "Inscripción {$calificacion['inscripcion_id']}: No pertenece al curso.";
                    continue;
                }

                NotaTarea::updateOrCreate(
                    [
                        'tarea_id' => $tarea->id,
                        'inscripcion_id' => $calificacion['inscripcion_id'],
                    ],
                    [
                        'nota' => $calificacion['nota'],
                    ]
                );

                $calificadas++;
            } catch (\Exception $e) {
                $errores[] = "Inscripción {$calificacion['inscripcion_id']}: Error al calificar.";
            }
        }

        $mensaje = "Se calificaron {$calificadas} estudiantes exitosamente.";

        if (!empty($errores)) {
            $mensaje .= " Errores: " . implode(', ', $errores);
        }

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje,
            'calificadas' => $calificadas,
            'errores' => $errores,
        ]);
    }

    /**
     * Duplicar tarea a otro curso
     */
    public function duplicar(Request $request, Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para duplicar esta tarea.');
        }

        $validated = $request->validate([
            'curso_destino_id' => ['required', 'exists:CURSO,id'],
            'fecha_asignacion' => ['required', 'date'],
        ], [
            'curso_destino_id.required' => 'Debe seleccionar un curso destino.',
            'fecha_asignacion.required' => 'Debe especificar la fecha de asignación.',
        ]);

        // Verificar que el curso destino pertenece al tutor
        $cursoDestino = Curso::find($validated['curso_destino_id']);
        if ($cursoDestino->tutor_id !== Auth::id()) {
            return back()->withErrors([
                'curso_destino_id' => 'No tienes permiso para crear tareas en ese curso.'
            ])->withInput();
        }

        $nuevaTarea = Tarea::create([
            'curso_id' => $validated['curso_destino_id'],
            'titulo' => $tarea->titulo . ' (Copia)',
            'descripcion' => $tarea->descripcion,
            'fecha_asignacion' => $validated['fecha_asignacion'],
        ]);

        return redirect()->route('tutor.tareas.show', $nuevaTarea)
            ->with('success', 'Tarea duplicada exitosamente.');
    }

    /**
     * Exportar calificaciones de la tarea
     */
    public function exportarCalificaciones(Tarea $tarea)
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $estudiantes = $tarea->curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) use ($tarea) {
                $nota = $tarea->notas->where('inscripcion_id', $inscripcion->id)->first();

                return [
                    'carnet' => $inscripcion->participante->carnet,
                    'nombre_completo' => $inscripcion->participante->nombre_completo,
                    'email' => $inscripcion->participante->email,
                    'tipo_participante' => $inscripcion->participante->tipoParticipante->descripcion,
                    'nota' => $nota?->nota ?? 'No entregada',
                    'calificacion' => $nota?->calificacion_literal ?? 'Sin calificar',
                    'fecha_calificacion' => $nota?->created_at?->format('d/m/Y H:i') ?? 'N/A',
                ];
            });

        return response()->json($estudiantes)
            ->header('Content-Disposition', "attachment; filename=\"calificaciones_{$tarea->titulo}.json\"");
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function getEstadisticasDashboard()
    {
        $tutor = Auth::user();
        $cursosIds = $tutor->cursos->pluck('id');

        $stats = [
            'total_tareas' => Tarea::whereIn('curso_id', $cursosIds)->count(),
            'tareas_este_mes' => Tarea::whereIn('curso_id', $cursosIds)
                ->whereMonth('fecha_asignacion', Carbon::now()->month)
                ->count(),
            'tareas_sin_calificar' => $this->getTareasSinCalificarCompleto($tutor),
            'promedio_calificaciones' => $this->getPromedioCalificacionesGeneral($tutor),
            'tareas_recientes' => Tarea::whereIn('curso_id', $cursosIds)
                ->with('curso')
                ->latest('fecha_asignacion')
                ->take(5)
                ->get(['id', 'titulo', 'curso_id', 'fecha_asignacion']),
        ];

        return response()->json($stats);
    }

    /**
     * Enriquecer tarea con estadísticas
     */
    private function enriquecerTareaConEstadisticas($tarea)
    {
        $tarea->estudiantes_total = $tarea->totalEstudiantesInscritos();
        $tarea->estudiantes_entregaron = $tarea->estudiantesQueEntregaron();
        $tarea->porcentaje_entrega = $tarea->porcentajeEntrega();
        $tarea->promedio_calificacion = round($tarea->promedioCalificaciones(), 2);
        $tarea->estado_calificacion = $this->determinarEstadoCalificacion($tarea);

        return $tarea;
    }

    /**
     * Determinar estado de calificación
     */
    private function determinarEstadoCalificacion($tarea)
    {
        $total = $tarea->totalEstudiantesInscritos();
        $calificadas = $tarea->estudiantesQueEntregaron();

        if ($calificadas === 0) {
            return ['estado' => 'sin_calificar', 'color' => 'red', 'texto' => 'Sin calificar'];
        } elseif ($calificadas < $total) {
            return ['estado' => 'parcial', 'color' => 'yellow', 'texto' => 'Parcialmente calificada'];
        } else {
            return ['estado' => 'completa', 'color' => 'green', 'texto' => 'Completamente calificada'];
        }
    }

    /**
     * Verificar si puede editar la tarea
     */
    private function puedeEditarTarea($tarea)
    {
        return $tarea->notas->isEmpty();
    }

    /**
     * Verificar si puede eliminar la tarea
     */
    private function puedeEliminarTarea($tarea)
    {
        return $tarea->notas->isEmpty();
    }

    /**
     * Obtener estadísticas generales de tareas del tutor
     */
    private function getEstadisticasTareas($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');

        return [
            'total_tareas' => Tarea::whereIn('curso_id', $cursosIds)->count(),
            'tareas_este_mes' => Tarea::whereIn('curso_id', $cursosIds)
                ->whereMonth('fecha_asignacion', Carbon::now()->month)
                ->count(),
            'sin_calificar' => $this->getTareasSinCalificarCompleto($tutor),
            'promedio_entregas' => $this->getPromedioEntregas($tutor),
        ];
    }

    /**
     * Obtener tareas sin calificar completamente
     */
    private function getTareasSinCalificarCompleto($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');

        return Tarea::whereIn('curso_id', $cursosIds)
            ->whereHas('curso.inscripciones', function ($q) {
                $q->where('estado', '!=', 'RETIRADO')
                    ->whereDoesntHave('notasTareas', function ($subQ) {
                        $subQ->whereColumn('tarea_id', 'TAREA.id');
                    });
            })
            ->count();
    }

    /**
     * Obtener promedio de entregas
     */
    private function getPromedioEntregas($tutor)
    {
        $tareas = Tarea::whereIn('curso_id', $tutor->cursos->pluck('id'))->get();

        if ($tareas->isEmpty()) {
            return 0;
        }

        $totalPorcentajes = $tareas->sum(function ($tarea) {
            return $tarea->porcentajeEntrega();
        });

        return round($totalPorcentajes / $tareas->count(), 2);
    }

    /**
     * Obtener promedio general de calificaciones del tutor
     */
    private function getPromedioCalificacionesGeneral($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');

        $promedio = NotaTarea::whereIn(
            'tarea_id',
            Tarea::whereIn('curso_id', $cursosIds)->pluck('id')
        )->avg('nota');

        return round($promedio ?? 0, 2);
    }
}
