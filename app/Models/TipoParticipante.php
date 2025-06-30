<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoParticipante extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'TIPO_PARTICIPANTE';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'codigo',
        'descripcion',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope para obtener solo tipos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Relación: Un tipo de participante tiene muchos participantes
     */
    public function participantes()
    {
        return $this->hasMany(Participante::class, 'tipo_participante_id');
    }

    /**
     * Relación: Un tipo de participante tiene muchos precios de curso
     */
    public function preciosCursos()
    {
        return $this->hasMany(PrecioCurso::class, 'tipo_participante_id');
    }

    /**
     * Accessor para obtener el nombre completo con código
     */
    public function getNombreCompletoAttribute()
    {
        return $this->codigo . ' - ' . $this->descripcion;
    }

    /**
     * Método genérico para verificar el tipo por código
     */
    public function esTipo($codigo)
    {
        return $this->codigo === $codigo;
    }

    /**
     * Método para verificar si es estudiante (cualquier tipo)
     */
    public function esEstudiante()
    {
        return str_starts_with($this->codigo, 'EST_');
    }

    /**
     * Método para verificar si es externo
     */
    public function esExterno()
    {
        return !$this->esEstudiante();
    }
}
