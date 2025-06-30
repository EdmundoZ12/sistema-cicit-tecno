<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaTarea extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'NOTA_TAREA';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'tarea_id',
        'inscripcion_id',
        'nota',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'nota' => 'decimal:2',
    ];

    /**
     * Scope para notas por tarea
     */
    public function scopePorTarea($query, $tareaId)
    {
        return $query->where('tarea_id', $tareaId);
    }

    /**
     * Scope para notas por inscripción
     */
    public function scopePorInscripcion($query, $inscripcionId)
    {
        return $query->where('inscripcion_id', $inscripcionId);
    }

    /**
     * Scope para notas aprobatorias (>= 51)
     */
    public function scopeAprobatorias($query)
    {
        return $query->where('nota', '>=', 51);
    }

    /**
     * Scope para notas reprobatorias (< 51)
     */
    public function scopeReprobatorias($query)
    {
        return $query->where('nota', '<', 51);
    }

    /**
     * Scope para notas excelentes (>= 90)
     */
    public function scopeExcelentes($query)
    {
        return $query->where('nota', '>=', 90);
    }

    /**
     * Relación: Una nota pertenece a una tarea
     */
    public function tarea()
    {
        return $this->belongsTo(Tarea::class, 'tarea_id');
    }

    /**
     * Relación: Una nota pertenece a una inscripción
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
     * Accessor para verificar si la nota es aprobatoria
     */
    public function getEsAprobatoriaAttribute()
    {
        return $this->nota >= 51;
    }

    /**
     * Accessor para verificar si la nota es excelente
     */
    public function getEsExcelenteAttribute()
    {
        return $this->nota >= 90;
    }

    /**
     * Accessor para obtener la calificación literal
     */
    public function getCalificacionLiteralAttribute()
    {
        if ($this->nota >= 90) return 'Excelente';
        if ($this->nota >= 80) return 'Muy Bueno';
        if ($this->nota >= 70) return 'Bueno';
        if ($this->nota >= 51) return 'Regular';
        return 'Insuficiente';
    }

    /**
     * Accessor para obtener el color de la nota (para UI)
     */
    public function getColorNotaAttribute()
    {
        if ($this->nota >= 90) return 'success'; // Verde
        if ($this->nota >= 80) return 'info';    // Azul
        if ($this->nota >= 70) return 'primary'; // Azul oscuro
        if ($this->nota >= 51) return 'warning'; // Amarillo
        return 'danger'; // Rojo
    }

    /**
     * Mutator para asegurar que la nota esté en rango válido (0-100)
     */
    public function setNotaAttribute($value)
    {
        $this->attributes['nota'] = max(0, min(100, (float) $value));
    }

    /**
     * Método para verificar si es la nota más alta de la tarea
     */
    public function esNotaMasAlta()
    {
        $notaMaxima = $this->tarea->notas()->max('nota');
        return $this->nota == $notaMaxima;
    }

    /**
     * Método para verificar si es la nota más baja de la tarea
     */
    public function esNotaMasBaja()
    {
        $notaMinima = $this->tarea->notas()->min('nota');
        return $this->nota == $notaMinima;
    }

    /**
     * Método para obtener la posición en el ranking de la tarea
     */
    public function getPosicionEnRanking()
    {
        return $this->tarea->notas()
            ->where('nota', '>', $this->nota)
            ->count() + 1;
    }

    /**
     * Método para obtener información completa de la calificación
     */
    public function getInformacionCompleta()
    {
        return [
            'nota' => $this->nota,
            'calificacion' => $this->calificacion_literal,
            'es_aprobatoria' => $this->es_aprobatoria,
            'color' => $this->color_nota,
            'participante' => $this->participante?->nombre_completo,
            'tarea' => $this->tarea?->titulo,
            'posicion_ranking' => $this->getPosicionEnRanking(),
        ];
    }
}
