<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Models\Tarea;
use App\Models\NotaTarea;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal del tutor
     */
    public function index(): Response
    {
        $tutor = Auth::user();
        
        // Obtener cursos del tutor
        $cursosIds = $tutor->cursos->pluck('id');
        
        // Estadísticas principales
        $stats = [
            // Cursos
            'total_cursos' => $tutor->cursos->count(),
            'cursos_activos' => $tutor->cursos()->activo()->count(),
            'cursos_en_progreso' => $tutor->cursos()
                ->where('fecha_inicio', '<=', now())
                ->where('fecha_fin', '>=', now())
                ->count(),
            
            // Estudiantes
            'total_estudiantes' => Inscripcion::whereIn('curso_id', $cursosIds)
                ->where('estado', '!=', 'RETIRADO')
                ->count(),
            'estudiantes_aprobados' => Inscripcion::whereIn('curso_id', $cursosIds)
                ->where('estado', 'APROBADO')
                ->count(),
            'estudiantes_reprobados' => Inscripcion::whereIn('curso_id', $cursosIds)
                ->where('estado', 'REPROBADO')
                ->count(),
            
            // Tareas y calificaciones
            'total_tareas' => Tarea::whereIn('curso_id', $cursosIds)->count(),
            'tareas_pendientes' => $this->getTareasPendientesCalificar($tutor),
            'calificaciones_esta_semana' => $this->getCalificacionesEstaSpanSemana($cursosIds),
            
            // Asistencias
            'asistencias_hoy' => $this->getAsistenciasHoy($cursosIds),
            'promedio_asistencia_general' => $this->getPromedioAsistenciaGeneral($cursosIds),
            
            // Rendimiento académico
            'promedio_general_cursos' => $this->getPromedioGeneralCursos($cursosIds),
            'estudiantes_bajo_rendimiento' => $this->getEstudiantesBajoRendimiento($cursosIds),
            'estudiantes_destacados' => $this->getEstudiantesDestacados($cursosIds),
        ];
        
        // Cursos recientes/destacados
        $cursosRecientes = $tutor->cursos()
            ->with(['inscripciones', 'tareas'])
            ->where('activo', true)
            ->orderBy('fecha_inicio', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'fecha_fin' => $curso->fecha_fin->format('d/m/Y'),
                    'total_estudiantes' => $curso->inscripciones->where('estado', '!=', 'RETIRADO')->count(),
                    'total_tareas' => $curso->tareas->count(),
                    'estado' => $this->determinarEstadoCurso($curso),
                    'progreso' => $this->calcularProgresoCurso($curso),
                ];
            });
        
        // Actividad reciente
        $actividadReciente = $this->getActividadReciente($cursosIds);
        
        // Tareas próximas a vencer
        $tareasProximas = $this->getTareasProximasVencer($cursosIds);
        
        // Estudiantes que necesitan atención
        $estudiantesAtencion = $this->getEstudiantesNecesitanAtencion($cursosIds);
        
        return Inertia::render('Tutor/Dashboard', [
            'estadisticas' => $stats,
            'cursos_recientes' => $cursosRecientes,
            'actividad_reciente' => $actividadReciente,
            'tareas_proximas' => $tareasProximas,
            'estudiantes_atencion' => $estudiantesAtencion,
        ]);
    }
    
    /**
     * Obtener tareas pendientes de calificar
     */
    private function getTareasPendientesCalificar($tutor)
    {
        $tareasIds = Tarea::whereIn('curso_id', $tutor->cursos->pluck('id'))->pluck('id');
        $inscripcionesIds = Inscripcion::whereIn('curso_id', $tutor->cursos->pluck('id'))
            ->where('estado', '!=', 'RETIRADO')
            ->pluck('id');
        
        // Calcular tareas que no tienen todas las notas
        $totalTareasEstudiantes = $tareasIds->count() * $inscripcionesIds->count();
        $notasExistentes = NotaTarea::whereIn('tarea_id', $tareasIds)
            ->whereIn('inscripcion_id', $inscripcionesIds)
            ->count();
        
        return max(0, $totalTareasEstudiantes - $notasExistentes);
    }
    
    /**
     * Obtener calificaciones de esta semana
     */
    private function getCalificacionesEstaSpanSemana($cursosIds)
    {
        $tareasIds = Tarea::whereIn('curso_id', $cursosIds)->pluck('id');
        
        return NotaTarea::whereIn('tarea_id', $tareasIds)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->count();
    }
    
    /**
     * Obtener asistencias marcadas hoy
     */
    private function getAsistenciasHoy($cursosIds)
    {
        return Asistencia::whereHas('inscripcion', function ($query) use ($cursosIds) {
            $query->whereIn('curso_id', $cursosIds);
        })
        ->whereDate('fecha', Carbon::today())
        ->count();
    }
    
    /**
     * Calcular promedio general de asistencia
     */
    private function getPromedioAsistenciaGeneral($cursosIds)
    {
        $inscripciones = Inscripcion::whereIn('curso_id', $cursosIds)
            ->where('estado', '!=', 'RETIRADO')
            ->get();
        
        if ($inscripciones->isEmpty()) {
            return 0;
        }
        
        $promedios = $inscripciones->map(function ($inscripcion) {
            return $inscripcion->porcentajeAsistencia();
        });
        
        return round($promedios->avg(), 2);
    }
    
    /**
     * Calcular promedio general de cursos
     */
    private function getPromedioGeneralCursos($cursosIds)
    {
        $tareasIds = Tarea::whereIn('curso_id', $cursosIds)->pluck('id');
        
        return round(NotaTarea::whereIn('tarea_id', $tareasIds)->avg('nota') ?? 0, 2);
    }
    
    /**
     * Obtener estudiantes con bajo rendimiento
     */
    private function getEstudiantesBajoRendimiento($cursosIds)
    {
        return Inscripcion::whereIn('curso_id', $cursosIds)
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->filter(function ($inscripcion) {
                return $inscripcion->promedioTareas() < 51;
            })
            ->count();
    }
    
    /**
     * Obtener estudiantes destacados
     */
    private function getEstudiantesDestacados($cursosIds)
    {
        return Inscripcion::whereIn('curso_id', $cursosIds)
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->filter(function ($inscripcion) {
                return $inscripcion->promedioTareas() >= 85;
            })
            ->count();
    }
    
    /**
     * Determinar estado del curso
     */
    private function determinarEstadoCurso($curso)
    {
        $ahora = Carbon::now();
        
        if ($ahora < $curso->fecha_inicio) {
            return 'próximo';
        } elseif ($ahora > $curso->fecha_fin) {
            return 'finalizado';
        } else {
            return 'en_progreso';
        }
    }
    
    /**
     * Calcular progreso del curso
     */
    private function calcularProgresoCurso($curso)
    {
        $ahora = Carbon::now();
        $inicio = $curso->fecha_inicio;
        $fin = $curso->fecha_fin;
        
        if ($ahora <= $inicio) {
            return 0;
        }
        
        if ($ahora >= $fin) {
            return 100;
        }
        
        $totalDias = $inicio->diffInDays($fin);
        $diasTranscurridos = $inicio->diffInDays($ahora);
        
        return round(($diasTranscurridos / $totalDias) * 100, 1);
    }
    
    /**
     * Obtener actividad reciente
     */
    private function getActividadReciente($cursosIds)
    {
        $actividades = collect();
        
        // Tareas creadas recientemente
        $tareasRecientes = Tarea::whereIn('curso_id', $cursosIds)
            ->with('curso')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($tarea) {
                return [
                    'tipo' => 'tarea_creada',
                    'titulo' => 'Nueva tarea: ' . $tarea->titulo,
                    'curso' => $tarea->curso->nombre,
                    'fecha' => $tarea->created_at->diffForHumans(),
                    'icono' => 'document-text',
                ];
            });
        
        // Calificaciones recientes
        $calificacionesRecientes = NotaTarea::whereHas('tarea', function ($query) use ($cursosIds) {
            $query->whereIn('curso_id', $cursosIds);
        })
        ->with(['tarea.curso', 'inscripcion.participante'])
        ->where('created_at', '>=', Carbon::now()->subDays(3))
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($nota) {
            return [
                'tipo' => 'calificacion',
                'titulo' => 'Calificación: ' . $nota->inscripcion->participante->nombre_completo,
                'curso' => $nota->tarea->curso->nombre,
                'fecha' => $nota->created_at->diffForHumans(),
                'icono' => 'academic-cap',
            ];
        });
        
        return $actividades->merge($tareasRecientes)
            ->merge($calificacionesRecientes)
            ->sortByDesc('fecha')
            ->take(10)
            ->values();
    }
    
    /**
     * Obtener tareas próximas a vencer
     */
    private function getTareasProximasVencer($cursosIds)
    {
        return Tarea::whereIn('curso_id', $cursosIds)
            ->with('curso')
            ->where('fecha_asignacion', '>=', Carbon::now())
            ->where('fecha_asignacion', '<=', Carbon::now()->addDays(7))
            ->orderBy('fecha_asignacion')
            ->limit(5)
            ->get()
            ->map(function ($tarea) {
                return [
                    'id' => $tarea->id,
                    'titulo' => $tarea->titulo,
                    'curso' => $tarea->curso->nombre,
                    'fecha_asignacion' => $tarea->fecha_asignacion->format('d/m/Y'),
                    'dias_restantes' => Carbon::now()->diffInDays($tarea->fecha_asignacion),
                ];
            });
    }
    
    /**
     * Obtener estudiantes que necesitan atención
     */
    private function getEstudiantesNecesitanAtencion($cursosIds)
    {
        return Inscripcion::whereIn('curso_id', $cursosIds)
            ->with(['participante', 'curso'])
            ->where('estado', '!=', 'RETIRADO')
            ->get()
            ->filter(function ($inscripcion) {
                $promedioTareas = $inscripcion->promedioTareas();
                $porcentajeAsistencia = $inscripcion->porcentajeAsistencia();
                
                return $promedioTareas < 51 || $porcentajeAsistencia < 75;
            })
            ->take(5)
            ->map(function ($inscripcion) {
                return [
                    'nombre' => $inscripcion->participante->nombre_completo,
                    'curso' => $inscripcion->curso->nombre,
                    'promedio_tareas' => round($inscripcion->promedioTareas(), 1),
                    'porcentaje_asistencia' => $inscripcion->porcentajeAsistencia(),
                    'motivo' => $inscripcion->promedioTareas() < 51 ? 'Bajo rendimiento académico' : 'Baja asistencia',
                ];
            })
            ->values();
    }
}
