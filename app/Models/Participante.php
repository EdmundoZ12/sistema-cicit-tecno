<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'PARTICIPANTE';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'carnet',
        'nombre',
        'apellido',
        'email',
        'telefono',
        'universidad',
        'tipo_participante_id',
        'activo',
        'registro',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Scope para obtener solo participantes activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por tipo de participante
     */
    public function scopePorTipo($query, $tipoId)
    {
        return $query->where('tipo_participante_id', $tipoId);
    }

    /**
     * Relación: Un participante pertenece a un tipo de participante
     */
    public function tipoParticipante()
    {
        return $this->belongsTo(TipoParticipante::class, 'tipo_participante_id');
    }

    /**
     * Relación: Un participante puede tener muchas preinscripciones
     */
    public function preinscripciones()
    {
        return $this->hasMany(Preinscripcion::class, 'participante_id');
    }

    /**
     * Relación: Un participante puede tener muchas inscripciones
     */
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'participante_id');
    }

    /**
     * Accessor para obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * Accessor para obtener la información completa
     */
    public function getInfoCompletaAttribute()
    {
        return $this->nombre_completo . ' (' . $this->carnet . ')';
    }

    /**
     * Mutator para normalizar el email
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    /**
     * Método para verificar si es estudiante FICCT
     */
    public function esFicct()
    {
        return $this->tipoParticipante?->esTipo('EST_FICCT') ?? false;
    }

    /**
     * Método para verificar si es estudiante UAGRM
     */
    public function esUagrm()
    {
        return $this->tipoParticipante?->esTipo('EST_UAGRM') ?? false;
    }

    /**
     * Método para verificar si es estudiante (cualquier tipo)
     */
    public function esEstudiante()
    {
        return $this->tipoParticipante?->esEstudiante() ?? false;
    }
}
