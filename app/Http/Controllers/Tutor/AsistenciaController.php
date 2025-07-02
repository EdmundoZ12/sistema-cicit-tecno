<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Display a listing of the resource - Vista de cursos para control de asistencia
     */
    public function index(Request $request): Response
    {
        $tutor = Auth::user();

        $cursos = $tutor->cursos()
            ->activo()
            ->with(['inscripciones' => function ($query) {
                $query->where('estado', '!=', 'RETIRADO')
                    ->with('participante');
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%");
            })
            ->when($request->estado, function ($query, $estado) {
                $hoy = Carbon::now();
                switch ($estado) {
                    case 'en_progreso':
                        $query->where('fecha_inicio', '<=', $hoy)
                            ->where('fecha_fin', '>=', $hoy);
                        break;
                    case 'proximos':
                        $query->where('fecha_inicio', '>', $hoy);
                        break;
                }
            })
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($curso) {
                return $this->enriquecerCursoConAsistencias($curso);
            });

        return Inertia::render('Tutor/Asistencias/Index', [
            'cursos' => $cursos,
            'filters' => $request->only(['search', 'estado']),
            'estadisticas' => $this->getEstadisticasAsistencia($tutor),
        ]);
    }

    /**
     * Mostrar control de asistencia para un curso específico
     */
    public function show(Curso $curso, Request $request): Response
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a este curso.');
        }

        $fecha = $request->input('fecha', Carbon::today()->toDateString());
        $fechaCarbon = Carbon::parse($fecha);

        // Verificar que la fecha esté dentro del rango del curso
        if ($fechaCarbon < $curso->fecha_inicio || $fechaCarbon > $curso->fecha_fin) {
            $fecha = Carbon::today()->toDateString();
            $fechaCarbon = Carbon::today();
        }

        // Obtener estudiantes inscritos y sus asistencias para la fecha
        $estudiantes = $curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) use ($fecha) {
                $asistencia = $inscripcion->asistencias()
                    ->where('fecha', $fecha)
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
                    'asistencia' => $asistencia ? [
                        'id' => $asistencia->id,
                        'estado' => $asistencia->estado,
                        'color' => $asistencia->color_estado,
                        'icono' => $asistencia->icono_estado,
                    ] : null,
                    'porcentaje_asistencia_general' => $inscripcion->porcentajeAsistencia(),
                ];
            });

        // Estadísticas del día
        $estadisticasDia = [
            'total_estudiantes' => $estudiantes->count(),
            'presentes' => $estudiantes->where('asistencia.estado', 'PRESENTE')->count(),
            'ausentes' => $estudiantes->where('asistencia.estado', 'AUSENTE')->count(),
            'justificados' => $estudiantes->where('asistencia.estado', 'JUSTIFICADO')->count(),
            'sin_marcar' => $estudiantes->whereNull('asistencia')->count(),
            'porcentaje_asistencia' => $this->calcularPorcentajeAsistenciaDia($estudiantes),
        ];

        // Obtener fechas de clases (días hábiles entre inicio y fin del curso)
        $fechasClases = $this->getFechasClases($curso);

        // Historial de asistencias del curso
        $historialAsistencias = $this->getHistorialAsistencias($curso, 10);

        return Inertia::render('Tutor/Asistencias/Show', [
            'curso' => $curso->load('gestion'),
            'fecha_actual' => $fecha,
            'estudiantes' => $estudiantes,
            'estadisticas_dia' => $estadisticasDia,
            'fechas_clases' => $fechasClases,
            'historial_asistencias' => $historialAsistencias,
            'puede_marcar_asistencia' => $this->puedeMarcarAsistencia($fechaCarbon, $curso),
        ]);
    }

    /**
     * Marcar asistencia individual
     */
    public function marcar(Request $request)
    {
        $validated = $request->validate([
            'inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'fecha' => ['required', 'date'],
            'estado' => ['required', 'in:PRESENTE,AUSENTE,JUSTIFICADO'],
        ], [
            'inscripcion_id.required' => 'La inscripción es obligatoria.',
            'inscripcion_id.exists' => 'La inscripción no es válida.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe ser válida.',
            'estado.required' => 'El estado de asistencia es obligatorio.',
            'estado.in' => 'El estado de asistencia no es válido.',
        ]);

        // Verificar permisos
        $inscripcion = Inscripcion::with('curso')->find($validated['inscripcion_id']);

        if ($inscripcion->curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Verificar que la fecha esté en el rango del curso
        $fecha = Carbon::parse($validated['fecha']);
        if ($fecha < $inscripcion->curso->fecha_inicio || $fecha > $inscripcion->curso->fecha_fin) {
            return response()->json([
                'error' => 'La fecha debe estar dentro del período del curso.'
            ], 422);
        }

        // Crear o actualizar asistencia
        $asistencia = Asistencia::updateOrCreate(
            [
                'inscripcion_id' => $validated['inscripcion_id'],
                'fecha' => $validated['fecha'],
            ],
            [
                'estado' => $validated['estado'],
            ]
        );

        return response()->json([
            'success' => true,
            'mensaje' => 'Asistencia marcada exitosamente.',
            'asistencia' => [
                'id' => $asistencia->id,
                'estado' => $asistencia->estado,
                'color' => $asistencia->color_estado,
                'icono' => $asistencia->icono_estado,
            ]
        ]);
    }

    /**
     * Marcar asistencia masiva para una fecha
     */
    public function marcarMasivo(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => ['required', 'exists:CURSO,id'],
            'fecha' => ['required', 'date'],
            'asistencias' => ['required', 'array', 'min:1'],
            'asistencias.*.inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'asistencias.*.estado' => ['required', 'in:PRESENTE,AUSENTE,JUSTIFICADO'],
        ], [
            'curso_id.required' => 'El curso es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'asistencias.required' => 'Debe marcar al menos una asistencia.',
            'asistencias.*.estado.in' => 'Estado de asistencia no válido.',
        ]);

        // Verificar permisos
        $curso = Curso::find($validated['curso_id']);
        if ($curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Verificar fecha
        $fecha = Carbon::parse($validated['fecha']);
        if ($fecha < $curso->fecha_inicio || $fecha > $curso->fecha_fin) {
            return response()->json([
                'error' => 'La fecha debe estar dentro del período del curso.'
            ], 422);
        }

        $marcadas = 0;
        $errores = [];

        DB::transaction(function () use ($validated, &$marcadas, &$errores) {
            foreach ($validated['asistencias'] as $asistenciaData) {
                try {
                    // Verificar que la inscripción pertenece al curso
                    $inscripcion = Inscripcion::find($asistenciaData['inscripcion_id']);

                    if ($inscripcion->curso_id != $validated['curso_id']) {
                        $errores[] = "Inscripción {$asistenciaData['inscripcion_id']}: No pertenece al curso.";
                        continue;
                    }

                    Asistencia::updateOrCreate(
                        [
                            'inscripcion_id' => $asistenciaData['inscripcion_id'],
                            'fecha' => $validated['fecha'],
                        ],
                        [
                            'estado' => $asistenciaData['estado'],
                        ]
                    );

                    $marcadas++;
                } catch (\Exception $e) {
                    $errores[] = "Inscripción {$asistenciaData['inscripcion_id']}: Error al marcar.";
                }
            }
        });

        $mensaje = "Se marcaron {$marcadas} asistencias exitosamente.";

        if (!empty($errores)) {
            $mensaje .= " Errores: " . implode(', ', $errores);
        }

        return response()->json([
            'success' => true,
            'mensaje' => $mensaje,
            'marcadas' => $marcadas,
            'errores' => $errores,
        ]);
    }

    /**
     * Marcar todos como presentes
     */
    public function marcarTodosPresentes(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => ['required', 'exists:CURSO,id'],
            'fecha' => ['required', 'date'],
        ]);

        $curso = Curso::find($validated['curso_id']);

        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $estudiantes = $curso->inscripciones()
            ->where('estado', '!=', 'RETIRADO')
            ->get();

        $marcadas = 0;

        DB::transaction(function () use ($validated, $estudiantes, &$marcadas) {
            foreach ($estudiantes as $inscripcion) {
                Asistencia::updateOrCreate(
                    [
                        'inscripcion_id' => $inscripcion->id,
                        'fecha' => $validated['fecha'],
                    ],
                    [
                        'estado' => 'PRESENTE',
                    ]
                );
                $marcadas++;
            }
        });

        return response()->json([
            'success' => true,
            'mensaje' => "Se marcaron {$marcadas} estudiantes como presentes.",
            'marcadas' => $marcadas,
        ]);
    }

    /**
     * Obtener reporte de asistencias por período
     */
    public function reporte(Curso $curso, Request $request)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $fechaInicio = $request->input('fecha_inicio', $curso->fecha_inicio->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());

        $estudiantes = $curso->inscripciones()
            ->with(['participante.tipoParticipante', 'asistencias' => function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
            }])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) use ($fechaInicio, $fechaFin) {
                $asistencias = $inscripcion->asistencias;
                $totalClases = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;

                return [
                    'participante' => [
                        'carnet' => $inscripcion->participante->carnet,
                        'nombre' => $inscripcion->participante->nombre_completo,
                        'email' => $inscripcion->participante->email,
                        'tipo' => $inscripcion->participante->tipoParticipante->codigo,
                    ],
                    'estadisticas' => [
                        'total_clases' => $totalClases,
                        'presentes' => $asistencias->where('estado', 'PRESENTE')->count(),
                        'ausentes' => $asistencias->where('estado', 'AUSENTE')->count(),
                        'justificadas' => $asistencias->where('estado', 'JUSTIFICADO')->count(),
                        'sin_marcar' => $totalClases - $asistencias->count(),
                        'porcentaje_asistencia' => $asistencias->count() > 0 ?
                            round((($asistencias->where('estado', 'PRESENTE')->count() + $asistencias->where('estado', 'JUSTIFICADO')->count()) / $asistencias->count()) * 100, 2) : 0,
                    ],
                    'asistencias_detalle' => $asistencias->map(function ($asistencia) {
                        return [
                            'fecha' => $asistencia->fecha->format('d/m/Y'),
                            'estado' => $asistencia->estado,
                            'dia_semana' => $asistencia->getDiaSemana(),
                        ];
                    }),
                ];
            });

        return response()->json([
            'curso' => $curso->only(['id', 'nombre']),
            'periodo' => [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin,
            ],
            'estudiantes' => $estudiantes,
            'resumen' => [
                'total_estudiantes' => $estudiantes->count(),
                'promedio_asistencia' => round($estudiantes->avg('estadisticas.porcentaje_asistencia'), 2),
            ],
        ]);
    }

    /**
     * Exportar asistencias
     */
    public function exportar(Curso $curso, Request $request)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $fechaInicio = $request->input('fecha_inicio', $curso->fecha_inicio->toDateString());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->toDateString());

        $reporte = $this->reporte($curso, $request);
        $data = json_decode($reporte->getContent(), true);

        return response()->json($data['estudiantes'])
            ->header('Content-Disposition', "attachment; filename=\"asistencias_{$curso->nombre}.json\"");
    }

    /**
     * Obtener estadísticas de asistencia para el dashboard
     */
    public function getEstadisticasDashboard()
    {
        $tutor = Auth::user();
        $cursosIds = $tutor->cursos->pluck('id');

        $stats = [
            'asistencias_hoy' => Asistencia::whereHas('inscripcion', function ($q) use ($cursosIds) {
                $q->whereIn('curso_id', $cursosIds);
            })->whereDate('fecha', Carbon::today())->count(),

            'promedio_asistencia_general' => $this->calcularPromedioAsistenciaGeneral($tutor),

            'estudiantes_con_baja_asistencia' => $this->getEstudiantesConBajaAsistencia($tutor),

            'clases_sin_marcar' => $this->getClasesSinMarcar($tutor),
        ];

        return response()->json($stats);
    }

    /**
     * Enriquecer curso con estadísticas de asistencia
     */
    private function enriquecerCursoConAsistencias($curso)
    {
        $totalEstudiantes = $curso->inscripciones->count();
        $curso->total_estudiantes = $totalEstudiantes;
        $curso->promedio_asistencia = $this->calcularPromedioAsistenciaCurso($curso);
        $curso->clases_con_asistencia_marcada = $this->getClasesConAsistenciaMarcada($curso);
        $curso->ultima_clase_marcada = $this->getUltimaClaseMarcada($curso);

        return $curso;
    }

    /**
     * Calcular promedio de asistencia de un curso
     */
    private function calcularPromedioAsistenciaCurso($curso)
    {
        $inscripciones = $curso->inscripciones;

        if ($inscripciones->isEmpty()) {
            return 0;
        }

        $totalPorcentajes = $inscripciones->sum(function ($inscripcion) {
            return $inscripcion->porcentajeAsistencia();
        });

        return round($totalPorcentajes / $inscripciones->count(), 2);
    }

    /**
     * Obtener fechas de clases (días hábiles)
     */
    private function getFechasClases($curso)
    {
        $fechas = [];
        $fecha = $curso->fecha_inicio->copy();

        while ($fecha <= $curso->fecha_fin && $fecha <= Carbon::now()) {
            if ($fecha->isWeekday()) { // Solo días hábiles
                $fechas[] = [
                    'fecha' => $fecha->toDateString(),
                    'formato' => $fecha->format('d/m/Y'),
                    'dia' => $fecha->locale('es')->dayName,
                    'es_hoy' => $fecha->isToday(),
                ];
            }
            $fecha->addDay();
        }

        return array_reverse($fechas); // Más recientes primero
    }

    /**
     * Obtener historial de asistencias
     */
    private function getHistorialAsistencias($curso, $limite = 10)
    {
        return Asistencia::whereHas('inscripcion', function ($q) use ($curso) {
            $q->where('curso_id', $curso->id);
        })
            ->selectRaw('fecha, COUNT(*) as total_marcadas')
            ->groupBy('fecha')
            ->orderBy('fecha', 'desc')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'total_marcadas' => $item->total_marcadas,
                ];
            });
    }

    /**
     * Verificar si puede marcar asistencia
     */
    private function puedeMarcarAsistencia($fecha, $curso)
    {
        return $fecha >= $curso->fecha_inicio &&
            $fecha <= $curso->fecha_fin &&
            $fecha <= Carbon::now();
    }

    /**
     * Calcular porcentaje de asistencia del día
     */
    private function calcularPorcentajeAsistenciaDia($estudiantes)
    {
        $total = $estudiantes->count();
        $presentes = $estudiantes->whereIn('asistencia.estado', ['PRESENTE', 'JUSTIFICADO'])->count();

        return $total > 0 ? round(($presentes / $total) * 100, 2) : 0;
    }

    /**
     * Obtener estadísticas generales de asistencia del tutor
     */
    private function getEstadisticasAsistencia($tutor)
    {
        $cursosIds = $tutor->cursos->pluck('id');

        return [
            'total_clases_impartidas' => Asistencia::whereHas('inscripcion', function ($q) use ($cursosIds) {
                $q->whereIn('curso_id', $cursosIds);
            })->distinct('fecha')->count('fecha'),

            'promedio_asistencia_general' => $this->calcularPromedioAsistenciaGeneral($tutor),

            'estudiantes_activos' => Inscripcion::whereIn('curso_id', $cursosIds)
                ->where('estado', '!=', 'RETIRADO')->count(),

            'asistencias_esta_semana' => Asistencia::whereHas('inscripcion', function ($q) use ($cursosIds) {
                $q->whereIn('curso_id', $cursosIds);
            })->whereBetween('fecha', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
        ];
    }

    /**
     * Calcular promedio general de asistencia del tutor
     */
    private function calcularPromedioAsistenciaGeneral($tutor)
    {
        $inscripciones = Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->where('estado', '!=', 'RETIRADO')
            ->get();

        if ($inscripciones->isEmpty()) {
            return 0;
        }

        $totalPorcentajes = $inscripciones->sum(function ($inscripcion) {
            return $inscripcion->porcentajeAsistencia();
        });

        return round($totalPorcentajes / $inscripciones->count(), 2);
    }

    /**
     * Obtener estudiantes con baja asistencia
     */
    private function getEstudiantesConBajaAsistencia($tutor)
    {
        return Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->filter(function ($inscripcion) {
                return $inscripcion->porcentajeAsistencia() < 75;
            })
            ->count();
    }

    /**
     * Obtener clases sin marcar asistencia
     */
    private function getClasesSinMarcar($tutor)
    {
        // Esta es una aproximación - puedes ajustarla según tu lógica de negocio
        $cursosActivos = $tutor->cursos()->enProgreso()->count();
        $clasesMaracadas = Asistencia::whereHas('inscripcion', function ($q) use ($tutor) {
            $q->whereIn('curso_id', $tutor->cursos->pluck('id'));
        })->whereDate('fecha', Carbon::today())->distinct('fecha')->count('fecha');

        return max(0, $cursosActivos - $clasesMaracadas);
    }

    /**
     * Obtener clases con asistencia marcada
     */
    private function getClasesConAsistenciaMarcada($curso)
    {
        return Asistencia::whereHas('inscripcion', function ($q) use ($curso) {
            $q->where('curso_id', $curso->id);
        })->distinct('fecha')->count('fecha');
    }

    /**
     * Obtener fecha de última clase marcada
     */
    private function getUltimaClaseMarcada($curso)
    {
        $ultimaFecha = Asistencia::whereHas('inscripcion', function ($q) use ($curso) {
            $q->where('curso_id', $curso->id);
        })->max('fecha');

        return $ultimaFecha ? Carbon::parse($ultimaFecha)->format('d/m/Y') : null;
    }
}
