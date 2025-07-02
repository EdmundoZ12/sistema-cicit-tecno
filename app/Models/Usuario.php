<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'USUARIO';

    /**
     * La clave primaria de la tabla
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el ID es auto-incrementable
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'carnet',
        'email',
        'telefono',
        'password',
        'rol',
        'activo',
        'registro',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Scope para usuarios activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por rol
     */
    public function scopeRole($query, $role)
    {
        return $query->where('rol', $role);
    }

    /**
     * Accessor para obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role)
    {
        return $this->rol === $role;
    }

    /**
     * Verificar si es RESPONSABLE
     */
    public function isResponsable()
    {
        return $this->hasRole('RESPONSABLE');
    }

    /**
     * Verificar si es ADMINISTRATIVO
     */
    public function isAdministrativo()
    {
        return $this->hasRole('ADMINISTRATIVO');
    }

    /**
     * Verificar si es TUTOR
     */
    public function isTutor()
    {
        return $this->hasRole('TUTOR');
    }

    /**
     * Relación con configuración de usuario (temas)
     */
    public function configuracion()
    {
        return $this->hasOne(ConfiguracionUsuario::class, 'usuario_id');
    }

    /**
     * Relación con cursos (si es tutor)
     */
    public function cursos()
    {
        return $this->hasMany(Curso::class, 'tutor_id');
    }

    /**
     * Relación con visitas de página
     */
    public function visitas()
    {
        return $this->hasMany(VisitaPagina::class, 'usuario_id');
    }

    /**
     * Relación con búsquedas realizadas
     */
    public function busquedas()
    {
        return $this->hasMany(BusquedaLog::class, 'usuario_id');
    }
}
