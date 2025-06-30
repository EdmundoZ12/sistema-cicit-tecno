<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asistencia extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'ASISTENCIA';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'inscripcion_id',
        'fecha',
        'estado',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Valores permitidos para el estado
     */
    const ESTADO_PRESENTE = 'PRESENTE';
    const ESTADO_AUSENTE = 'AUSENTE';
    const ESTADO_JUSTIFICADO = 'JUSTIFICADO';

    /**
     * Scope para asistencias por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para asistencias presentes
     */
    public function scopePresentes($query)
    {
        return $query->where('estado', self::ESTADO_PRESENTE);
    }

    /**
     * Scope para asistencias ausentes
     */
    public function scopeAusentes($query)
    {
        return $query->where('estado', self::ESTADO_AUSENTE);
    }

    /**
     * Scope para asistencias justificadas
     */
    public function scopeJustificadas($query)
    {
        return $query->where('estado', self::ESTADO_JUSTIFICADO);
    }

    /**
     * Scope para asistencias por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para asistencias por inscripción
     */
    public function scopePorInscripcion($query, $inscripcionId)
    {
        return $query->where('inscripcion_id', $inscripcionId);
    }

    /**
     * Scope para asistencias del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha', Carbon::now()->month)
            ->whereYear('fecha', Carbon::now()->year);
    }

    /**
     * Relación: Una asistencia pertenece a una inscripción
     */
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id');
    }

    /**
     * Accessor para obtener el participante via inscripción
     */
    public function getParticipanteAttribute()
    {
        return $this->inscripcion?->participante;
    }

    /**
     * Accessor para obtener el curso via inscripción
     */
    public function getCursoAttribute()
    {
        return $this->inscripcion?->curso;
    }

    /**
     * Accessor para verificar si fue hoy
     */
    public function getFueHoyAttribute()
    {
        return $this->fecha->isToday();
    }

    /**
     * Accessor para obtener el color del estado (para UI)
     */
    public function getColorEstadoAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_PRESENTE => 'success',      // Verde
            self::ESTADO_JUSTIFICADO => 'warning',   // Amarillo
            self::ESTADO_AUSENTE => 'danger',        // Rojo
            default => 'secondary'                   // Gris
        };
    }

    /**
     * Accessor para obtener el ícono del estado
     */
    public function getIconoEstadoAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_PRESENTE => 'check-circle',
            self::ESTADO_JUSTIFICADO => 'exclamation-circle',
            self::ESTADO_AUSENTE => 'x-circle',
            default => 'question-circle'
        };
    }

    /**
     * Método para verificar si está presente
     */
    public function estaPresente()
    {
        return $this->estado === self::ESTADO_PRESENTE;
    }

    /**
     * Método para verificar si está ausente
     */
    public function estaAusente()
    {
        return $this->estado === self::ESTADO_AUSENTE;
    }

    /**
     * Método para verificar si está justificado
     */
    public function estaJustificado()
    {
        return $this->estado === self::ESTADO_JUSTIFICADO;
    }

    /**
     * Método para verificar si cuenta como asistencia válida (presente o justificado)
     */
    public function esAsistenciaValida()
    {
        return in_array($this->estado, [self::ESTADO_PRESENTE, self::ESTADO_JUSTIFICADO]);
    }

    /**
     * Método para obtener el nombre del día de la semana
     */
    public function getDiaSemana()
    {
        return $this->fecha->locale('es')->dayName;
    }

    /**
     * Método para verificar si es un día hábil
     */
    public function esDiaHabil()
    {
        return $this->fecha->isWeekday();
    }

    /**
     * Método estático para obtener estadísticas de asistencia por curso
     */
    public static function estadisticasPorCurso($cursoId)
    {
        $inscripciones = Inscripcion::where('curso_id', $cursoId)->activas()->get();
        $totalClases = static::whereHas('inscripcion', function ($query) use ($cursoId) {
            $query->where('curso_id', $cursoId);
        })->distinct('fecha')->count('fecha');

        $estadisticas = [];
        foreach ($inscripciones as $inscripcion) {
            $asistencias = $inscripcion->asistencias;
            $presentes = $asistencias->where('estado', self::ESTADO_PRESENTE)->count();
            $justificadas = $asistencias->where('estado', self::ESTADO_JUSTIFICADO)->count();
            $ausentes = $asistencias->where('estado', self::ESTADO_AUSENTE)->count();

            $estadisticas[] = [
                'participante' => $inscripcion->participante->nombre_completo,
                'presentes' => $presentes,
                'justificadas' => $justificadas,
                'ausentes' => $ausentes,
                'total_clases' => $totalClases,
                'porcentaje' => $totalClases > 0 ? round((($presentes + $justificadas) / $totalClases) * 100, 2) : 0
            ];
        }

        return $estadisticas;
    }
}
