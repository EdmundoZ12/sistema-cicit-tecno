<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Inscripcion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'INSCRIPCION';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'participante_id',
        'curso_id',
        'preinscripcion_id',
        'fecha_inscripcion',
        'nota_final',
        'estado',
        'observaciones',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_inscripcion' => 'datetime',
        'nota_final' => 'decimal:2',
    ];

    /**
     * Valores permitidos para el estado
     */
    const ESTADO_INSCRITO = 'INSCRITO';
    const ESTADO_APROBADO = 'APROBADO';
    const ESTADO_REPROBADO = 'REPROBADO';
    const ESTADO_RETIRADO = 'RETIRADO';

    /**
     * Scope para inscripciones por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para inscripciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    /**
     * Scope para inscripciones reprobadas
     */
    public function scopeReprobadas($query)
    {
        return $query->where('estado', self::ESTADO_REPROBADO);
    }

    /**
     * Scope para inscripciones activas (no retiradas)
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', '!=', self::ESTADO_RETIRADO);
    }

    /**
     * Scope para filtrar por curso
     */
    public function scopePorCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    /**
     * Relación: Una inscripción pertenece a un participante
     */
    public function participante()
    {
        return $this->belongsTo(Participante::class, 'participante_id');
    }

    /**
     * Relación: Una inscripción pertenece a un curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación: Una inscripción pertenece a una preinscripción
     */
    public function preinscripcion()
    {
        return $this->belongsTo(Preinscripcion::class, 'preinscripcion_id');
    }

    /**
     * Relación: Una inscripción tiene muchas asistencias
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'inscripcion_id');
    }

    /**
     * Relación: Una inscripción tiene muchas notas de tareas
     */
    public function notasTareas()
    {
        return $this->hasMany(NotaTarea::class, 'inscripcion_id');
    }

    /**
     * Relación: Una inscripción puede tener muchos certificados
     */
    public function certificados()
    {
        return $this->hasMany(Certificado::class, 'inscripcion_id');
    }

    /**
     * Accessor para verificar si tiene nota final
     */
    public function getTieneNotaFinalAttribute()
    {
        return $this->nota_final !== null;
    }

    /**
     * Accessor para obtener días desde la inscripción
     */
    public function getDiasDesdeInscripcionAttribute()
    {
        return $this->fecha_inscripcion->diffInDays(Carbon::now());
    }

    /**
     * Método para verificar si está inscrito (activo)
     */
    public function estaInscrito()
    {
        return $this->estado === self::ESTADO_INSCRITO;
    }

    /**
     * Método para verificar si aprobó el curso
     */
    public function aprobo()
    {
        return $this->estado === self::ESTADO_APROBADO;
    }

    /**
     * Método para verificar si reprobó el curso
     */
    public function reprobo()
    {
        return $this->estado === self::ESTADO_REPROBADO;
    }

    /**
     * Método para verificar si se retiró del curso
     */
    public function seRetiro()
    {
        return $this->estado === self::ESTADO_RETIRADO;
    }

    /**
     * Método para calcular el porcentaje de asistencia
     */
    public function porcentajeAsistencia()
    {
        $totalClases = $this->asistencias()->count();
        $clasesPresente = $this->asistencias()
            ->where('estado', 'PRESENTE')
            ->count();

        return $totalClases > 0 ? round(($clasesPresente / $totalClases) * 100, 2) : 0;
    }

    /**
     * Método para obtener el promedio de notas de tareas
     */
    public function promedioTareas()
    {
        return $this->notasTareas()->avg('nota') ?? 0;
    }

    /**
     * Método para verificar si puede obtener certificado
     */
    public function puedeObtenerCertificado()
    {
        return $this->aprobo() && $this->porcentajeAsistencia() >= 75;
    }

    /**
     * Eventos del modelo para manejar cupos del curso
     */
    protected static function booted()
    {
        // Cuando se crea una inscripción activa, incrementar cupos ocupados
        static::created(function ($inscripcion) {
            if ($inscripcion->estaInscrito()) {
                $inscripcion->curso->increment('cupos_ocupados');
            }
        });

        // Cuando se actualiza una inscripción
        static::updated(function ($inscripcion) {
            $original = $inscripcion->getOriginal();
            $estadoAnterior = $original['estado'];
            $estadoActual = $inscripcion->estado;

            // Si cambió de no inscrito a inscrito
            if ($estadoAnterior !== self::ESTADO_INSCRITO && $estadoActual === self::ESTADO_INSCRITO) {
                $inscripcion->curso->increment('cupos_ocupados');
            }
            // Si cambió de inscrito a no inscrito (retirado, etc.)
            elseif ($estadoAnterior === self::ESTADO_INSCRITO && $estadoActual !== self::ESTADO_INSCRITO) {
                $inscripcion->curso->decrement('cupos_ocupados');
            }
        });

        // Cuando se elimina una inscripción activa, decrementar cupos ocupados
        static::deleted(function ($inscripcion) {
            if ($inscripcion->estaInscrito()) {
                $inscripcion->curso->decrement('cupos_ocupados');
            }
        });
    }
}
