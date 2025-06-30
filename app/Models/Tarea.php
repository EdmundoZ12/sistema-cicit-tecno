<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Tarea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'TAREA';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'curso_id',
        'titulo',
        'descripcion',
        'fecha_asignacion',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_asignacion' => 'date',
    ];

    /**
     * Scope para tareas por curso
     */
    public function scopePorCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    /**
     * Scope para tareas por fecha de asignación
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_asignacion', $fecha);
    }

    /**
     * Scope para tareas recientes (últimos 30 días)
     */
    public function scopeRecientes($query)
    {
        return $query->where('fecha_asignacion', '>=', Carbon::now()->subDays(30));
    }

    /**
     * Scope para ordenar por fecha de asignación
     */
    public function scopeOrdenadoPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha_asignacion', $direccion);
    }

    /**
     * Relación: Una tarea pertenece a un curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación: Una tarea tiene muchas notas (calificaciones)
     */
    public function notas()
    {
        return $this->hasMany(NotaTarea::class, 'tarea_id');
    }

    /**
     * Accessor para obtener el tutor del curso
     */
    public function getTutorAttribute()
    {
        return $this->curso?->tutor;
    }

    /**
     * Accessor para obtener días desde la asignación
     */
    public function getDiasDesdeAsignacionAttribute()
    {
        return $this->fecha_asignacion->diffInDays(Carbon::now());
    }

    /**
     * Accessor para verificar si es una tarea nueva (menos de 7 días)
     */
    public function getEsNuevaAttribute()
    {
        return $this->fecha_asignacion->diffInDays(Carbon::now()) <= 7;
    }

    /**
     * Método para verificar si la tarea fue asignada hoy
     */
    public function fueAsignadaHoy()
    {
        return $this->fecha_asignacion->isToday();
    }

    /**
     * Método para obtener el promedio de calificaciones
     */
    public function promedioCalificaciones()
    {
        return $this->notas()->avg('nota') ?? 0;
    }

    /**
     * Método para obtener el número de estudiantes que entregaron
     */
    public function estudiantesQueEntregaron()
    {
        return $this->notas()->count();
    }

    /**
     * Método para obtener el número total de estudiantes inscritos en el curso
     */
    public function totalEstudiantesInscritos()
    {
        return $this->curso?->inscripciones()->activas()->count() ?? 0;
    }

    /**
     * Método para obtener el porcentaje de entrega
     */
    public function porcentajeEntrega()
    {
        $total = $this->totalEstudiantesInscritos();
        $entregadas = $this->estudiantesQueEntregaron();

        return $total > 0 ? round(($entregadas / $total) * 100, 2) : 0;
    }

    /**
     * Método para verificar si todos los estudiantes entregaron
     */
    public function todosEntregaron()
    {
        return $this->porcentajeEntrega() == 100;
    }

    /**
     * Método para obtener estadísticas de la tarea
     */
    public function getEstadisticas()
    {
        return [
            'promedio' => round($this->promedioCalificaciones(), 2),
            'entregadas' => $this->estudiantesQueEntregaron(),
            'total_estudiantes' => $this->totalEstudiantesInscritos(),
            'porcentaje_entrega' => $this->porcentajeEntrega(),
            'nota_maxima' => $this->notas()->max('nota') ?? 0,
            'nota_minima' => $this->notas()->min('nota') ?? 0,
        ];
    }
}
