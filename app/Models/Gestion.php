<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Gestion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'GESTION';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre',
        'descripcion',
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
    ];

    /**
     * Scope para obtener solo gestiones activas
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener la gestión actual
     */
    public function scopeActual($query)
    {
        $hoy = Carbon::now()->toDateString();
        return $query->where('activo', true)
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy);
    }

    /**
     * Scope para gestiones futuras
     */
    public function scopeFutura($query)
    {
        return $query->where('activo', true)
            ->where('fecha_inicio', '>', Carbon::now()->toDateString());
    }

    /**
     * Scope para gestiones pasadas
     */
    public function scopePasada($query)
    {
        return $query->where('fecha_fin', '<', Carbon::now()->toDateString());
    }

    /**
     * Relación: Una gestión tiene muchos cursos
     */
    public function cursos()
    {
        return $this->hasMany(Curso::class, 'gestion_id');
    }

    /**
     * Accessor para obtener el período completo
     */
    public function getPeriodoCompletoAttribute()
    {
        return $this->fecha_inicio->format('d/m/Y') . ' - ' . $this->fecha_fin->format('d/m/Y');
    }

    /**
     * Accessor para obtener el año de la gestión
     */
    public function getAnioAttribute()
    {
        return $this->fecha_inicio->year;
    }

    /**
     * Método para verificar si la gestión está actualmente en curso
     */
    public function estaEnCurso()
    {
        $hoy = Carbon::now();
        return $this->activo &&
            $hoy->greaterThanOrEqualTo($this->fecha_inicio) &&
            $hoy->lessThanOrEqualTo($this->fecha_fin);
    }

    /**
     * Método para verificar si la gestión ya finalizó
     */
    public function haFinalizado()
    {
        return Carbon::now()->greaterThan($this->fecha_fin);
    }

    /**
     * Método para verificar si la gestión aún no ha comenzado
     */
    public function noHaComenzado()
    {
        return Carbon::now()->lessThan($this->fecha_inicio);
    }

    /**
     * Método para obtener la duración en días
     */
    public function duracionEnDias()
    {
        return $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }
}
