<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\NotaTarea;
use App\Models\Tarea;
use App\Models\Inscripcion;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class NotaController extends Controller
{
    /**
     * Display a listing of the resource - Vista general de calificaciones
     */
    public function index(Request $request): Response
    {
        $tutor = Auth::user();

        // Obtener tareas del tutor con información de calificaciones
        $tareas = Tarea::with(['curso.gestion', 'notas.inscripcion.participante'])
            ->whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->when($request->search, function ($query, $search) {
                $query->where('titulo', 'ILIKE', "%{$search}%")
                    ->orWhereHas('curso', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->when($request->estado_calificacion, function ($query, $estado) {
                switch ($estado) {
                    case 'sin_calificar':
                        $query->whereDoesntHave('notas');
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
            ->withCount([
                'notas',
                'notas as notas_aprobatorias_count' => function ($query) {
                    $query->where('nota', '>=', 51);
                }
            ])
            ->orderBy('fecha_asignacion', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Enriquecer tareas con estadísticas de calificación
        $tareas->getCollection()->transform(function ($tarea) {
            return $this->enriquecerTareaConEstadisticasNotas($tarea);
        });

        return Inertia::render('Tutor/Notas/Index', [
            'tareas' => $tareas,
            'filters' => $request->only(['search', 'curso_id', 'estado_calificacion']),
            'cursos' => $tutor->cursos()->activo()->get(['id', 'nombre']),
            'estadisticas_generales' => $this->getEstadisticasGeneralesNotas($tutor),
        ]);
    }

    /**
     * Mostrar formulario de calificación para una tarea específica
     */
    public function show(Tarea $tarea): Response
    {
        // Verificar permisos
        if ($tarea->curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para calificar esta tarea.');
        }

        $tarea->load(['curso.gestion']);

        // Obtener estudiantes y sus calificaciones
        $estudiantes = $tarea->curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) use ($tarea) {
                $nota = NotaTarea::where('tarea_id', $tarea->id)
                    ->where('inscripcion_id', $inscripcion->id)
                    ->first();

                return [
                    'inscripcion_id' => $inscripcion->id,
                    'participante' => [
                        'id' => $inscripcion->participante->id,
                        'nombre' => $inscripcion->participante->nombre_completo,
                        'carnet' => $inscripcion->participante->carnet,
                        'email' => $inscripcion->participante->email,
                        'tipo' => $inscripcion->participante->tipoParticipante->codigo,
                    ],
                    'nota_actual' => $nota ? [
                        'id' => $nota->id,
                        'valor' => $nota->nota,
                        'calificacion' => $nota->calificacion_literal,
                        'color' => $nota->color_nota,
                        'fecha_calificacion' => $nota->updated_at->format('d/m/Y H:i'),
                        'es_aprobatoria' => $nota->es_aprobatoria,
                    ] : null,
                    'promedio_general' => round($inscripcion->promedioTareas(), 2),
                    'tiene_nota' => $nota !== null,
                ];
            });

        // Estadísticas de la tarea
        $estadisticas = [
            'total_estudiantes' => $estudiantes->count(),
            'calificados' => $estudiantes->where('tiene_nota', true)->count(),
            'pendientes' => $estudiantes->where('tiene_nota', false)->count(),
            'promedio_general' => round($tarea->promedioCalificaciones(), 2),
            'nota_maxima' => $tarea->notas->max('nota') ?? 0,
            'nota_minima' => $tarea->notas->min('nota') ?? 0,
            'aprobados' => $tarea->notas->where('nota', '>=', 51)->count(),
            'reprobados' => $tarea->notas->where('nota', '<', 51)->count(),
            'porcentaje_calificado' => $estudiantes->count() > 0 ?
                round(($estudiantes->where('tiene_nota', true)->count() / $estudiantes->count()) * 100, 2) : 0,
        ];

        return Inertia::render('Tutor/Notas/Show', [
            'tarea' => $tarea,
            'estudiantes' => $estudiantes,
            'estadisticas' => $estadisticas,
            'puede_calificar_masivo' => $estudiantes->where('tiene_nota', false)->count() > 0,
        ]);
    }

    /**
     * Calificar o actualizar nota individual
     */
    public function calificar(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => ['required', 'exists:TAREA,id'],
            'inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'nota' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'tarea_id.required' => 'La tarea es obligatoria.',
            'inscripcion_id.required' => 'La inscripción es obligatoria.',
            'nota.required' => 'La nota es obligatoria.',
            'nota.numeric' => 'La nota debe ser un número.',
            'nota.min' => 'La nota no puede ser menor a 0.',
            'nota.max' => 'La nota no puede ser mayor a 100.',
        ]);

        // Verificar permisos
        $tarea = Tarea::find($validated['tarea_id']);
        if ($tarea->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Verificar que la inscripción pertenece al curso de la tarea
        $inscripcion = Inscripcion::find($validated['inscripcion_id']);
        if ($inscripcion->curso_id !== $tarea->curso_id) {
            return response()->json([
                'error' => 'La inscripción no pertenece al curso de esta tarea.'
            ], 422);
        }

        // Crear o actualizar la nota
        $nota = NotaTarea::updateOrCreate(
            [
                'tarea_id' => $validated['tarea_id'],
                'inscripcion_id' => $validated['inscripcion_id'],
            ],
            [
                'nota' => $validated['nota'],
            ]
        );

        return response()->json([
            'success' => true,
            'mensaje' => 'Nota guardada exitosamente.',
            'nota' => [
                'id' => $nota->id,
                'valor' => $nota->nota,
                'calificacion' => $nota->calificacion_literal,
                'color' => $nota->color_nota,
                'es_aprobatoria' => $nota->es_aprobatoria,
            ]
        ]);
    }

    /**
     * Calificar múltiples estudiantes a la vez
     */
    public function calificarMasivo(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => ['required', 'exists:TAREA,id'],
            'calificaciones' => ['required', 'array', 'min:1'],
            'calificaciones.*.inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'calificaciones.*.nota' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'tarea_id.required' => 'La tarea es obligatoria.',
            'calificaciones.required' => 'Debe proporcionar calificaciones.',
            'calificaciones.*.nota.required' => 'Todas las notas son obligatorias.',
            'calificaciones.*.nota.numeric' => 'Las notas deben ser números.',
            'calificaciones.*.nota.min' => 'Las notas no pueden ser menores a 0.',
            'calificaciones.*.nota.max' => 'Las notas no pueden ser mayores a 100.',
        ]);

        // Verificar permisos
        $tarea = Tarea::find($validated['tarea_id']);
        if ($tarea->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $calificadas = 0;
        $errores = [];

        DB::transaction(function () use ($validated, $tarea, &$calificadas, &$errores) {
            foreach ($validated['calificaciones'] as $calificacion) {
                try {
                    // Verificar que la inscripción pertenece al curso
                    $inscripcion = Inscripcion::find($calificacion['inscripcion_id']);

                    if ($inscripcion->curso_id !== $tarea->curso_id) {
                        $errores[] = "Inscripción {$calificacion['inscripcion_id']}: No pertenece al curso.";
                        continue;
                    }

                    NotaTarea::updateOrCreate(
                        [
                            'tarea_id' => $validated['tarea_id'],
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
        });

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
     * Aplicar nota a todos los estudiantes
     */
    public function aplicarNotaATodos(Request $request)
    {
        $validated = $request->validate([
            'tarea_id' => ['required', 'exists:TAREA,id'],
            'nota' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'tarea_id.required' => 'La tarea es obligatoria.',
            'nota.required' => 'La nota es obligatoria.',
            'nota.numeric' => 'La nota debe ser un número.',
            'nota.min' => 'La nota no puede ser menor a 0.',
            'nota.max' => 'La nota no puede ser mayor a 100.',
        ]);

        // Verificar permisos
        $tarea = Tarea::find($validated['tarea_id']);
        if ($tarea->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $estudiantes = $tarea->curso->inscripciones()
            ->where('estado', '!=', 'RETIRADO')
            ->get();

        $calificadas = 0;

        DB::transaction(function () use ($validated, $estudiantes, &$calificadas) {
            foreach ($estudiantes as $inscripcion) {
                NotaTarea::updateOrCreate(
                    [
                        'tarea_id' => $validated['tarea_id'],
                        'inscripcion_id' => $inscripcion->id,
                    ],
                    [
                        'nota' => $validated['nota'],
                    ]
                );
                $calificadas++;
            }
        });

        return response()->json([
            'success' => true,
            'mensaje' => "Se aplicó la nota {$validated['nota']} a {$calificadas} estudiantes.",
            'calificadas' => $calificadas,
        ]);
    }

    /**
     * Eliminar calificación
     */
    public function eliminarNota(Request $request)
    {
        $validated = $request->validate([
            'nota_id' => ['required', 'exists:NOTA_TAREA,id'],
        ]);

        $nota = NotaTarea::find($validated['nota_id']);

        // Verificar permisos
        if ($nota->tarea->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $participante = $nota->participante->nombre_completo;
        $nota->delete();

        return response()->json([
            'success' => true,
            'mensaje' => "Calificación de {$participante} eliminada exitosamente.",
        ]);
    }

    /**
     * Obtener resumen de calificaciones por curso
     */
    public function resumenPorCurso(Curso $curso)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $tareas = $curso->tareas()->with(['notas.inscripcion.participante'])->get();

        $resumen = $tareas->map(function ($tarea) {
            $notas = $tarea->notas;

            return [
                'tarea' => $tarea->titulo,
                'fecha_asignacion' => $tarea->fecha_asignacion->format('d/m/Y'),
                'total_estudiantes' => $tarea->totalEstudiantesInscritos(),
                'calificados' => $notas->count(),
                'pendientes' => $tarea->totalEstudiantesInscritos() - $notas->count(),
                'promedio' => round($notas->avg('nota') ?? 0, 2),
                'aprobados' => $notas->where('nota', '>=', 51)->count(),
                'reprobados' => $notas->where('nota', '<', 51)->count(),
                'nota_maxima' => $notas->max('nota') ?? 0,
                'nota_minima' => $notas->min('nota') ?? 0,
            ];
        });

        return response()->json([
            'curso' => $curso->only(['id', 'nombre']),
            'resumen_tareas' => $resumen,
            'estadisticas_generales' => [
                'total_tareas' => $tareas->count(),
                'promedio_general_curso' => round($tareas->avg(function ($tarea) {
                    return $tarea->promedioCalificaciones();
                }), 2),
                'total_calificaciones' => $tareas->sum(function ($tarea) {
                    return $tarea->notas->count();
                }),
            ],
        ]);
    }

    /**
     * Exportar calificaciones del curso
     */
    public function exportarCurso(Curso $curso)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $inscripciones = $curso->inscripciones()
            ->with(['participante.tipoParticipante', 'notasTareas.tarea'])
            ->where('estado', '!=', 'RETIRADO')
            ->get();

        $datos = $inscripciones->map(function ($inscripcion) use ($curso) {
            $notasPorTarea = [];
            foreach ($curso->tareas as $tarea) {
                $nota = $inscripcion->notasTareas->where('tarea_id', $tarea->id)->first();
                $notasPorTarea[$tarea->titulo] = $nota ? $nota->nota : 'Sin calificar';
            }

            return [
                'carnet' => $inscripcion->participante->carnet,
                'nombre_completo' => $inscripcion->participante->nombre_completo,
                'email' => $inscripcion->participante->email,
                'tipo_participante' => $inscripcion->participante->tipoParticipante->descripcion,
                'promedio_tareas' => round($inscripcion->promedioTareas(), 2),
                'nota_final' => $inscripcion->nota_final ?? 'Sin asignar',
                'estado' => $inscripcion->estado,
                ...$notasPorTarea,
            ];
        });

        return response()->json($datos)
            ->header('Content-Disposition', "attachment; filename=\"calificaciones_{$curso->nombre}.json\"");
    }

    /**
     * Estadísticas para dashboard
     */
    public function getEstadisticasDashboard()
    {
        $tutor = Auth::user();
        $cursosIds = $tutor->cursos->pluck('id');

        $stats = [
            'tareas_pendientes_calificar' => $this->getTareasPendientesCalificar($tutor),
            'promedio_general_tutor' => $this->getPromedioGeneralTutor($tutor),
            'total_calificaciones_mes' => NotaTarea::whereIn(
                'tarea_id',
                Tarea::whereIn('curso_id', $cursosIds)->pluck('id')
            )->whereMonth('created_at', Carbon::now()->month)->count(),
            'estudiantes_bajo_rendimiento' => $this->getEstudiantesBajoRendimiento($tutor),
        ];

        return response()->json($stats);
    }

    /**
     * Enriquecer tarea con estadísticas de notas
     */
    private function enriquecerTareaConEstadisticasNotas($tarea)
    {
        $totalEstudiantes = $tarea->totalEstudiantesInscritos();
        $calificados = $tarea->notas_count;
        $pendientes = $totalEstudiantes - $calificados;

        $tarea->total_estudiantes = $totalEstudiantes;
        $tarea->estudiantes_calificados = $calificados;
        $tarea->estudiantes_pendientes = $pendientes;
        $tarea->porcentaje_calificado = $totalEstudiantes > 0 ?
            round(($calificados / $totalEstudiantes) * 100, 2) : 0;
        $tarea->promedio_nota = round($tarea->promedioCalificaciones(), 2);
        $tarea->aprobados = $tarea->notas_aprobatorias_count;
        $tarea->estado_calificacion = $this->determinarEstadoCalificacion($tarea);

        return $tarea;
    }

    /**
     * Determinar estado de calificación de la tarea
     */
    private function determinarEstadoCalificacion($tarea)
    {
        $porcentaje = $tarea->porcentaje_calificado ?? 0;

        if ($porcentaje === 0) {
            return ['estado' => 'sin_calificar', 'color' => 'red', 'texto' => 'Sin calificar'];
        } elseif ($porcentaje < 100) {
            return ['estado' => 'parcial', 'color' => 'yellow', 'texto' => 'Parcialmente calificada'];
        } else {
            return ['estado' => 'completa', 'color' => 'green', 'texto' => 'Completamente calificada'];
        }
    }

    /**
     * Obtener estadísticas generales de notas del tutor
     */
    private function getEstadisticasGeneralesNotas($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');
        $tareasIds = Tarea::whereIn('curso_id', $cursosIds)->pluck('id');

        return [
            'total_calificaciones' => NotaTarea::whereIn('tarea_id', $tareasIds)->count(),
            'calificaciones_este_mes' => NotaTarea::whereIn('tarea_id', $tareasIds)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count(),
            'promedio_general' => round(NotaTarea::whereIn('tarea_id', $tareasIds)->avg('nota') ?? 0, 2),
            'tareas_sin_calificar' => $this->getTareasPendientesCalificar($tutor),
        ];
    }

    /**
     * Obtener tareas pendientes de calificar
     */
    private function getTareasPendientesCalificar($tutor)
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
     * Obtener promedio general del tutor
     */
    private function getPromedioGeneralTutor($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');
        $tareasIds = Tarea::whereIn('curso_id', $cursosIds)->pluck('id');

        return round(NotaTarea::whereIn('tarea_id', $tareasIds)->avg('nota') ?? 0, 2);
    }

    /**
     * Obtener estudiantes con bajo rendimiento
     */
    private function getEstudiantesBajoRendimiento($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');

        return Inscripcion::whereIn('curso_id', $cursosIds)
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->filter(function ($inscripcion) {
                return $inscripcion->promedioTareas() < 51;
            })
            ->count();
    }
}
