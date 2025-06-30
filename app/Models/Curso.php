<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Curso extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'CURSO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_horas',
        'nivel',
        'logo_url',
        'tutor_id',
        'gestion_id',
        'aula',
        'cupos_totales',
        'cupos_ocupados',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'duracion_horas' => 'integer',
        'cupos_totales' => 'integer',
        'cupos_ocupados' => 'integer',
    ];

    /**
     * Scope para obtener solo cursos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para cursos en progreso
     */
    public function scopeEnProgreso($query)
    {
        $hoy = Carbon::now()->toDateString();
        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy);
    }

    /**
     * Scope para cursos por tutor
     */
    public function scopePorTutor($query, $tutorId)
    {
        return $query->where('tutor_id', $tutorId);
    }

    /**
     * Scope para cursos por gestión
     */
    public function scopePorGestion($query, $gestionId)
    {
        return $query->where('gestion_id', $gestionId);
    }

    /**
     * Relación: Un curso pertenece a un tutor (usuario)
     */
    public function tutor()
    {
        return $this->belongsTo(Usuario::class, 'tutor_id');
    }

    /**
     * Relación: Un curso pertenece a una gestión
     */
    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    /**
     * Relación: Un curso tiene muchos precios por tipo de participante
     */
    public function precios()
    {
        return $this->hasMany(PrecioCurso::class, 'curso_id');
    }

    /**
     * Relación: Un curso tiene muchas preinscripciones
     */
    public function preinscripciones()
    {
        return $this->hasMany(Preinscripcion::class, 'curso_id');
    }

    /**
     * Relación: Un curso tiene muchas inscripciones
     */
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'curso_id');
    }

    /**
     * Relación: Un curso tiene muchas tareas
     */
    public function tareas()
    {
        return $this->hasMany(Tarea::class, 'curso_id');
    }

    /**
     * Accessor para obtener cupos disponibles
     */
    public function getCuposDisponiblesAttribute()
    {
        return $this->cupos_totales - $this->cupos_ocupados;
    }

    /**
     * Accessor para obtener porcentaje de ocupación
     */
    public function getPorcentajeOcupacionAttribute()
    {
        return $this->cupos_totales > 0
            ? round(($this->cupos_ocupados / $this->cupos_totales) * 100, 2)
            : 0;
    }

    /**
     * Accessor para obtener el período del curso
     */
    public function getPeriodoCursoAttribute()
    {
        return $this->fecha_inicio->format('d/m/Y') . ' - ' . $this->fecha_fin->format('d/m/Y');
    }

    /**
     * Método para verificar si el curso está en progreso
     */
    public function estaEnProgreso()
    {
        $hoy = Carbon::now();
        return $this->activo &&
            $hoy->greaterThanOrEqualTo($this->fecha_inicio) &&
            $hoy->lessThanOrEqualTo($this->fecha_fin);
    }

    /**
     * Método para verificar si hay cupos disponibles
     */
    public function tieneCuposDisponibles()
    {
        return $this->cupos_disponibles > 0;
    }

    /**
     * Método para verificar si el curso está completo
     */
    public function estaCompleto()
    {
        return $this->cupos_ocupados >= $this->cupos_totales;
    }

    /**
     * Método para obtener el precio por tipo de participante
     */
    public function precioParaTipo($tipoParticipanteId)
    {
        return $this->precios()
            ->where('tipo_participante_id', $tipoParticipanteId)
            ->where('activo', true)
            ->first()?->precio ?? 0;
    }
}
