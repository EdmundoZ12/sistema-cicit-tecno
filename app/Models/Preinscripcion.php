<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Preinscripcion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'PREINSCRIPCION';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'participante_id',
        'curso_id',
        'fecha_preinscripcion',
        'estado',
        'observaciones',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_preinscripcion' => 'datetime',
    ];

    /**
     * Valores permitidos para el estado
     */
    const ESTADO_PENDIENTE = 'PENDIENTE';
    const ESTADO_APROBADA = 'APROBADA';
    const ESTADO_RECHAZADA = 'RECHAZADA';

    /**
     * Scope para preinscripciones pendientes
     */
    public function scopePendiente($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Scope para preinscripciones aprobadas
     */
    public function scopeAprobada($query)
    {
        return $query->where('estado', self::ESTADO_APROBADA);
    }

    /**
     * Scope para preinscripciones rechazadas
     */
    public function scopeRechazada($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADA);
    }

    /**
     * Scope para filtrar por curso
     */
    public function scopePorCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    /**
     * Scope para filtrar por participante
     */
    public function scopePorParticipante($query, $participanteId)
    {
        return $query->where('participante_id', $participanteId);
    }

    /**
     * Relación: Una preinscripción pertenece a un participante
     */
    public function participante()
    {
        return $this->belongsTo(Participante::class, 'participante_id');
    }

    /**
     * Relación: Una preinscripción pertenece a un curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación: Una preinscripción puede tener un pago
     */
    public function pago()
    {
        return $this->hasOne(Pago::class, 'preinscripcion_id');
    }

    /**
     * Relación: Una preinscripción aprobada puede tener una inscripción
     */
    public function inscripcion()
    {
        return $this->hasOne(Inscripcion::class, 'preinscripcion_id');
    }

    /**
     * Accessor para obtener días desde la preinscripción
     */
    public function getDiasDesdePreinscripcionAttribute()
    {
        return $this->fecha_preinscripcion->diffInDays(Carbon::now());
    }

    /**
     * Método para verificar si está pendiente
     */
    public function estaPendiente()
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    /**
     * Método para verificar si está aprobada
     */
    public function estaAprobada()
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    /**
     * Método para verificar si está rechazada
     */
    public function estaRechazada()
    {
        return $this->estado === self::ESTADO_RECHAZADA;
    }

    /**
     * Método para verificar si tiene pago registrado
     */
    public function tienePago()
    {
        return $this->pago !== null;
    }

    /**
     * Método para verificar si ya se convirtió en inscripción
     */
    public function tieneInscripcion()
    {
        return $this->inscripcion !== null;
    }

    /**
     * Método para obtener el precio aplicable según el tipo de participante
     */
    public function getPrecioAplicable()
    {
        return $this->curso->precioParaTipo($this->participante->tipo_participante_id);
    }
}
