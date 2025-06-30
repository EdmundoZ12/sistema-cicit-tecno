<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'MENU_ITEM';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'titulo',
        'ruta',
        'icono',
        'orden',
        'padre_id',
        'rol',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Roles disponibles para el menú
     */
    const ROL_RESPONSABLE = 'RESPONSABLE';
    const ROL_ADMINISTRATIVO = 'ADMINISTRATIVO';
    const ROL_TUTOR = 'TUTOR';
    const ROL_TODOS = 'TODOS';

    /**
     * Scope para elementos activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para elementos por rol
     */
    public function scopePorRol($query, $rol)
    {
        return $query->where(function ($q) use ($rol) {
            $q->where('rol', $rol)
                ->orWhere('rol', self::ROL_TODOS);
        });
    }

    /**
     * Scope para elementos principales (sin padre)
     */
    public function scopePrincipales($query)
    {
        return $query->whereNull('padre_id');
    }

    /**
     * Scope para submenús (con padre)
     */
    public function scopeSubmenu($query)
    {
        return $query->whereNotNull('padre_id');
    }

    /**
     * Scope para ordenar por orden
     */
    public function scopeOrdenadoPorOrden($query)
    {
        return $query->orderBy('orden');
    }

    /**
     * Relación: Un elemento puede tener un padre
     */
    public function padre()
    {
        return $this->belongsTo(MenuItem::class, 'padre_id');
    }

    /**
     * Relación: Un elemento puede tener muchos hijos
     */
    public function hijos()
    {
        return $this->hasMany(MenuItem::class, 'padre_id')->orderBy('orden');
    }

    /**
     * Relación: Hijos activos ordenados
     */
    public function hijosActivos()
    {
        return $this->hasMany(MenuItem::class, 'padre_id')
            ->where('activo', true)
            ->orderBy('orden');
    }

    /**
     * Accessor para verificar si tiene hijos
     */
    public function getTieneHijosAttribute()
    {
        return $this->hijos()->count() > 0;
    }

    /**
     * Accessor para verificar si es un submenú
     */
    public function getEsSubmenuAttribute()
    {
        return $this->padre_id !== null;
    }

    /**
     * Accessor para obtener el nivel de profundidad
     */
    public function getNivelAttribute()
    {
        $nivel = 0;
        $padre = $this->padre;

        while ($padre) {
            $nivel++;
            $padre = $padre->padre;
        }

        return $nivel;
    }

    /**
     * Método para verificar si el usuario puede ver este elemento
     */
    public function puedeVerUsuario($usuario)
    {
        if (!$this->activo) {
            return false;
        }

        return $this->rol === self::ROL_TODOS ||
            $this->rol === $usuario->rol;
    }

    /**
     * Método para obtener menú completo por rol
     */
    public static function getMenuPorRol($rol)
    {
        return static::activo()
            ->porRol($rol)
            ->principales()
            ->ordenadoPorOrden()
            ->with(['hijosActivos' => function ($query) use ($rol) {
                $query->porRol($rol)->ordenadoPorOrden();
            }])
            ->get();
    }

    /**
     * Método para obtener breadcrumbs
     */
    public function getBreadcrumbs()
    {
        $breadcrumbs = [];
        $item = $this;

        while ($item) {
            array_unshift($breadcrumbs, [
                'titulo' => $item->titulo,
                'ruta' => $item->ruta,
            ]);
            $item = $item->padre;
        }

        return $breadcrumbs;
    }

    /**
     * Método para verificar si es una ruta activa
     */
    public function esRutaActiva($rutaActual)
    {
        if ($this->ruta === $rutaActual) {
            return true;
        }

        // Verificar si algún hijo coincide
        return $this->hijosActivos()
            ->where('ruta', $rutaActual)
            ->exists();
    }

    /**
     * Método para obtener estructura jerárquica completa
     */
    public static function getEstructuraJerarquica($rol = null)
    {
        $query = static::activo()->principales()->ordenadoPorOrden();

        if ($rol) {
            $query->porRol($rol);
        }

        return $query->with(['hijosActivos' => function ($query) use ($rol) {
            if ($rol) {
                $query->porRol($rol);
            }
            $query->ordenadoPorOrden();
        }])->get();
    }
}
