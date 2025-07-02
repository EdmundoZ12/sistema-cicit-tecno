<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Asistencia;
use App\Models\Tarea;
use App\Models\NotaTarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class MisCursosController extends Controller
{
    /**
     * Display a listing of the resource - Cursos del tutor autenticado
     */
    public function index(Request $request): Response
    {
        $tutor = Auth::user();

        $cursos = Curso::where('tutor_id', $tutor->id)
            ->with(['gestion', 'inscripciones.participante', 'tareas'])
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            })
            ->when($request->estado, function ($query, $estado) {
                $hoy = Carbon::now();
                switch ($estado) {
                    case 'activos':
                        $query->where('activo', true);
                        break;
                    case 'en_progreso':
                        $query->where('activo', true)
                            ->where('fecha_inicio', '<=', $hoy)
                            ->where('fecha_fin', '>=', $hoy);
                        break;
                    case 'proximos':
                        $query->where('activo', true)
                            ->where('fecha_inicio', '>', $hoy);
                        break;
                    case 'finalizados':
                        $query->where('fecha_fin', '<', $hoy);
                        break;
                }
            })
            ->when($request->gestion_id, function ($query, $gestionId) {
                $query->where('gestion_id', $gestionId);
            })
            ->withCount([
                'inscripciones as inscripciones_activas_count' => function ($query) {
                    $query->where('estado', '!=', 'RETIRADO');
                },
                'tareas'
            ])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Enriquecer cada curso con estadísticas
        $cursos->getCollection()->transform(function ($curso) {
            return $this->enriquecerCursoConEstadisticas($curso);
        });

        return Inertia::render('Tutor/MisCursos/Index', [
            'cursos' => $cursos,
            'filters' => $request->only(['search', 'estado', 'gestion_id']),
            'gestiones' => \App\Models\Gestion::activo()->get(['id', 'nombre']),
            'estadisticas_generales' => $this->getEstadisticasGeneralesTutor($tutor),
        ]);
    }

    /**
     * Display the specified resource - Vista detallada del curso
     */
    public function show(Curso $curso): Response
    {
        // Verificar que el curso pertenece al tutor autenticado
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a este curso.');
        }

        $curso->load([
            'gestion',
            'inscripciones.participante.tipoParticipante',
            'inscripciones.asistencias',
            'inscripciones.notasTareas.tarea',
            'tareas.notas',
            'precios.tipoParticipante'
        ]);

        // Estadísticas detalladas del curso
        $estadisticasCurso = [
            'total_estudiantes' => $curso->inscripciones->where('estado', '!=', 'RETIRADO')->count(),
            'estudiantes_aprobados' => $curso->inscripciones->where('estado', 'APROBADO')->count(),
            'estudiantes_reprobados' => $curso->inscripciones->where('estado', 'REPROBADO')->count(),
            'estudiantes_retirados' => $curso->inscripciones->where('estado', 'RETIRADO')->count(),
            'promedio_asistencia' => $this->calcularPromedioAsistenciaCurso($curso),
            'total_tareas' => $curso->tareas->count(),
            'promedio_notas' => $this->calcularPromedioNotasCurso($curso),
            'porcentaje_ocupacion' => $curso->porcentaje_ocupacion,
            'dias_restantes' => $curso->fecha_fin->isFuture() ?
                Carbon::now()->diffInDays($curso->fecha_fin) : 0,
            'estado_curso' => $this->determinarEstadoCurso($curso),
        ];

        // Lista de estudiantes con su progreso
        $estudiantesProgreso = $curso->inscripciones
            ->where('estado', '!=', 'RETIRADO')
            ->map(function ($inscripcion) use ($curso) {
                return [
                    'inscripcion_id' => $inscripcion->id,
                    'participante' => $inscripcion->participante->nombre_completo,
                    'carnet' => $inscripcion->participante->carnet,
                    'email' => $inscripcion->participante->email,
                    'tipo_participante' => $inscripcion->participante->tipoParticipante->codigo,
                    'estado' => $inscripcion->estado,
                    'nota_final' => $inscripcion->nota_final,
                    'porcentaje_asistencia' => $inscripcion->porcentajeAsistencia(),
                    'promedio_tareas' => round($inscripcion->promedioTareas(), 2),
                    'tareas_completadas' => $inscripcion->notasTareas->count(),
                    'total_tareas' => $curso->tareas->count(),
                    'puede_aprobar' => $this->puedeAprobarEstudiante($inscripcion),
                ];
            });

        // Resumen de tareas
        $resumenTareas = $curso->tareas->map(function ($tarea) {
            return [
                'id' => $tarea->id,
                'titulo' => $tarea->titulo,
                'fecha_asignacion' => $tarea->fecha_asignacion->format('d/m/Y'),
                'estudiantes_entregaron' => $tarea->estudiantesQueEntregaron(),
                'total_estudiantes' => $tarea->totalEstudiantesInscritos(),
                'porcentaje_entrega' => $tarea->porcentajeEntrega(),
                'promedio_calificacion' => round($tarea->promedioCalificaciones(), 2),
                'es_nueva' => $tarea->es_nueva,
            ];
        });

        return Inertia::render('Tutor/MisCursos/Show', [
            'curso' => $curso,
            'estadisticas' => $estadisticasCurso,
            'estudiantes' => $estudiantesProgreso,
            'resumen_tareas' => $resumenTareas,
            'puede_editar' => $this->puedeEditarCurso($curso),
        ]);
    }

    /**
     * Obtener estudiantes de un curso específico
     */
    public function getEstudiantes(Curso $curso)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $estudiantes = $curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'id' => $inscripcion->id,
                    'participante_id' => $inscripcion->participante_id,
                    'nombre' => $inscripcion->participante->nombre_completo,
                    'carnet' => $inscripcion->participante->carnet,
                    'email' => $inscripcion->participante->email,
                    'tipo' => $inscripcion->participante->tipoParticipante->codigo,
                    'estado' => $inscripcion->estado,
                    'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('d/m/Y'),
                ];
            });

        return response()->json($estudiantes);
    }

    /**
     * Obtener progreso académico de un estudiante
     */
    public function getProgresoEstudiante(Curso $curso, Inscripcion $inscripcion)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id() || $inscripcion->curso_id !== $curso->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $progreso = [
            'estudiante' => [
                'nombre' => $inscripcion->participante->nombre_completo,
                'carnet' => $inscripcion->participante->carnet,
                'email' => $inscripcion->participante->email,
                'estado' => $inscripcion->estado,
            ],
            'asistencias' => [
                'total_clases' => $inscripcion->asistencias->count(),
                'presentes' => $inscripcion->asistencias->where('estado', 'PRESENTE')->count(),
                'ausentes' => $inscripcion->asistencias->where('estado', 'AUSENTE')->count(),
                'justificadas' => $inscripcion->asistencias->where('estado', 'JUSTIFICADO')->count(),
                'porcentaje' => $inscripcion->porcentajeAsistencia(),
            ],
            'tareas' => $inscripcion->notasTareas->map(function ($nota) {
                return [
                    'tarea' => $nota->tarea->titulo,
                    'nota' => $nota->nota,
                    'calificacion' => $nota->calificacion_literal,
                    'fecha_entrega' => $nota->created_at->format('d/m/Y'),
                ];
            }),
            'resumen' => [
                'promedio_tareas' => round($inscripcion->promedioTareas(), 2),
                'nota_final' => $inscripcion->nota_final,
                'puede_aprobar' => $this->puedeAprobarEstudiante($inscripcion),
            ],
        ];

        return response()->json($progreso);
    }

    /**
     * Actualizar nota final de un estudiante
     */
    public function actualizarNotaFinal(Request $request, Curso $curso, Inscripcion $inscripcion)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id() || $inscripcion->curso_id !== $curso->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'nota_final' => ['required', 'numeric', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'nota_final.required' => 'La nota final es obligatoria.',
            'nota_final.numeric' => 'La nota final debe ser un número.',
            'nota_final.min' => 'La nota final no puede ser menor a 0.',
            'nota_final.max' => 'La nota final no puede ser mayor a 100.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        // Determinar estado según la nota
        $nuevoEstado = $validated['nota_final'] >= 51 ? 'APROBADO' : 'REPROBADO';

        $inscripcion->update([
            'nota_final' => $validated['nota_final'],
            'estado' => $nuevoEstado,
            'observaciones' => $validated['observaciones'],
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Nota final actualizada exitosamente.',
            'estado' => $nuevoEstado,
        ]);
    }

    /**
     * Obtener resumen rápido para dashboard
     */
    public function getResumenDashboard()
    {
        $tutor = Auth::user();

        $resumen = [
            'cursos_activos' => $tutor->cursos()->activo()->count(),
            'cursos_en_progreso' => $tutor->cursos()->enProgreso()->count(),
            'total_estudiantes' => Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
                ->where('estado', '!=', 'RETIRADO')
                ->count(),
            'tareas_sin_calificar' => $this->getTareasSinCalificar($tutor),
            'proxima_clase' => $this->getProximaClase($tutor),
        ];

        return response()->json($resumen);
    }

    /**
     * Exportar lista de estudiantes
     */
    public function exportarEstudiantes(Curso $curso)
    {
        // Verificar permisos
        if ($curso->tutor_id !== Auth::id()) {
            abort(403, 'No autorizado');
        }

        $estudiantes = $curso->inscripciones()
            ->with(['participante.tipoParticipante'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'carnet' => $inscripcion->participante->carnet,
                    'nombre_completo' => $inscripcion->participante->nombre_completo,
                    'email' => $inscripcion->participante->email,
                    'telefono' => $inscripcion->participante->telefono,
                    'tipo_participante' => $inscripcion->participante->tipoParticipante->descripcion,
                    'estado' => $inscripcion->estado,
                    'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('d/m/Y'),
                    'nota_final' => $inscripcion->nota_final ?? 'N/A',
                    'promedio_tareas' => round($inscripcion->promedioTareas(), 2),
                    'porcentaje_asistencia' => $inscripcion->porcentajeAsistencia(),
                ];
            });

        return response()->json($estudiantes)
            ->header('Content-Disposition', "attachment; filename=\"estudiantes_{$curso->nombre}.json\"");
    }

    /**
     * Enriquecer curso con estadísticas adicionales
     */
    private function enriquecerCursoConEstadisticas($curso)
    {
        $curso->estado_visual = $this->determinarEstadoCurso($curso);
        $curso->progreso_porcentaje = $this->calcularProgresoCurso($curso);
        $curso->estudiantes_activos = $curso->inscripciones_activas_count;
        $curso->promedio_asistencia = $this->calcularPromedioAsistenciaCurso($curso);
        $curso->tareas_pendientes = $this->getTareasPendientesCurso($curso);

        return $curso;
    }

    /**
     * Determinar estado visual del curso
     */
    private function determinarEstadoCurso($curso)
    {
        $hoy = Carbon::now();

        if (!$curso->activo) {
            return ['estado' => 'inactivo', 'color' => 'gray', 'texto' => 'Inactivo'];
        }

        if ($curso->fecha_fin < $hoy) {
            return ['estado' => 'finalizado', 'color' => 'blue', 'texto' => 'Finalizado'];
        }

        if ($curso->fecha_inicio > $hoy) {
            return ['estado' => 'proximo', 'color' => 'yellow', 'texto' => 'Próximo a iniciar'];
        }

        return ['estado' => 'en_progreso', 'color' => 'green', 'texto' => 'En progreso'];
    }

    /**
     * Calcular progreso del curso (% de tiempo transcurrido)
     */
    private function calcularProgresoCurso($curso)
    {
        $hoy = Carbon::now();

        if ($hoy < $curso->fecha_inicio) {
            return 0;
        }

        if ($hoy > $curso->fecha_fin) {
            return 100;
        }

        $totalDias = $curso->fecha_inicio->diffInDays($curso->fecha_fin);
        $diasTranscurridos = $curso->fecha_inicio->diffInDays($hoy);

        return $totalDias > 0 ? round(($diasTranscurridos / $totalDias) * 100, 1) : 0;
    }

    /**
     * Calcular promedio de asistencia del curso
     */
    private function calcularPromedioAsistenciaCurso($curso)
    {
        $inscripciones = $curso->inscripciones->where('estado', '!=', 'RETIRADO');

        if ($inscripciones->isEmpty()) {
            return 0;
        }

        $totalPorcentajes = $inscripciones->sum(function ($inscripcion) {
            return $inscripcion->porcentajeAsistencia();
        });

        return round($totalPorcentajes / $inscripciones->count(), 2);
    }

    /**
     * Calcular promedio de notas del curso
     */
    private function calcularPromedioNotasCurso($curso)
    {
        $notas = NotaTarea::whereIn(
            'inscripcion_id',
            $curso->inscripciones->where('estado', '!=', 'RETIRADO')->pluck('id')
        )->avg('nota');

        return round($notas ?? 0, 2);
    }

    /**
     * Verificar si puede aprobar un estudiante
     */
    private function puedeAprobarEstudiante($inscripcion)
    {
        return $inscripcion->porcentajeAsistencia() >= 75 &&
            $inscripcion->promedioTareas() >= 51;
    }

    /**
     * Verificar si puede editar el curso
     */
    private function puedeEditarCurso($curso)
    {
        return $curso->activo && $curso->fecha_inicio > Carbon::now();
    }

    /**
     * Obtener estadísticas generales del tutor
     */
    private function getEstadisticasGeneralesTutor($tutor)
    {
        return [
            'total_cursos' => $tutor->cursos->count(),
            'cursos_activos' => $tutor->cursos->where('activo', true)->count(),
            'cursos_en_progreso' => $tutor->cursos()->enProgreso()->count(),
            'total_estudiantes' => Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
                ->where('estado', '!=', 'RETIRADO')->count(),
            'estudiantes_aprobados' => Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
                ->where('estado', 'APROBADO')->count(),
        ];
    }

    /**
     * Obtener tareas sin calificar
     */
    private function getTareasSinCalificar($tutor)
    {
        $tareasDelTutor = Tarea::whereIn('curso_id', $tutor->cursos->pluck('id'))->pluck('id');
        $inscripcionesDelTutor = Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->where('estado', '!=', 'RETIRADO')->pluck('id');

        // Calcular tareas que no tienen todas las notas
        return $tareasDelTutor->count() * $inscripcionesDelTutor->count() -
            NotaTarea::whereIn('tarea_id', $tareasDelTutor)
            ->whereIn('inscripcion_id', $inscripcionesDelTutor)
            ->count();
    }

    /**
     * Obtener próxima clase
     */
    private function getProximaClase($tutor)
    {
        $proximoCurso = $tutor->cursos()
            ->where('fecha_inicio', '>', Carbon::now())
            ->orderBy('fecha_inicio')
            ->first();

        return $proximoCurso ? [
            'curso' => $proximoCurso->nombre,
            'fecha' => $proximoCurso->fecha_inicio->format('d/m/Y'),
            'aula' => $proximoCurso->aula,
        ] : null;
    }

    /**
     * Obtener tareas pendientes de un curso
     */
    private function getTareasPendientesCurso($curso)
    {
        return $curso->tareas()
            ->whereDate('fecha_asignacion', '>=', Carbon::now()->subDays(7))
            ->count();
    }
}
