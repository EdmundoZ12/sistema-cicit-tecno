<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioCurso extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'PRECIO_CURSO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'curso_id',
        'tipo_participante_id',
        'precio',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Scope para obtener solo precios activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por curso
     */
    public function scopePorCurso($query, $cursoId)
    {
        return $query->where('curso_id', $cursoId);
    }

    /**
     * Scope para filtrar por tipo de participante
     */
    public function scopePorTipo($query, $tipoId)
    {
        return $query->where('tipo_participante_id', $tipoId);
    }

    /**
     * Relación: Un precio pertenece a un curso
     */
    public function curso()
    {
        return $this->belongsTo(Curso::class, 'curso_id');
    }

    /**
     * Relación: Un precio pertenece a un tipo de participante
     */
    public function tipoParticipante()
    {
        return $this->belongsTo(TipoParticipante::class, 'tipo_participante_id');
    }

    /**
     * Accessor para obtener el precio formateado
     */
    public function getPrecioFormateadoAttribute()
    {
        return 'Bs. ' . number_format($this->precio, 2);
    }

    /**
     * Accessor para obtener información completa del precio
     */
    public function getInfoCompletaAttribute()
    {
        return $this->tipoParticipante->descripcion . ': ' . $this->precio_formateado;
    }

    /**
     * Mutator para asegurar que el precio sea positivo
     */
    public function setPrecioAttribute($value)
    {
        $this->attributes['precio'] = max(0, (float) $value);
    }

    /**
     * Método para verificar si es el precio más bajo del curso
     */
    public function esPrecioMasBajo()
    {
        $precioMinimo = $this->curso->precios()
            ->where('activo', true)
            ->min('precio');

        return $this->precio == $precioMinimo;
    }

    /**
     * Método para verificar si es el precio más alto del curso
     */
    public function esPrecioMasAlto()
    {
        $precioMaximo = $this->curso->precios()
            ->where('activo', true)
            ->max('precio');

        return $this->precio == $precioMaximo;
    }
}
