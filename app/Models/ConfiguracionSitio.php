<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionSitio extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'CONFIGURACION_SITIO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Tipos de configuración disponibles
     */
    const TIPO_STRING = 'string';
    const TIPO_NUMBER = 'number';
    const TIPO_BOOLEAN = 'boolean';
    const TIPO_JSON = 'json';

    /**
     * Claves de configuración del CICIT
     */
    const NOMBRE_SITIO = 'nombre_sitio';
    const NOMBRE_CORTO = 'nombre_corto';
    const DESCRIPCION_SITIO = 'descripcion_sitio';
    const MISION = 'mision';
    const VISION = 'vision';
    const EMAIL_CONTACTO = 'email_contacto';
    const TELEFONO_CONTACTO = 'telefono_contacto';
    const DIRECCION = 'direccion';
    const HORARIO_ATENCION = 'horario_atencion';
    const LOGO_PRINCIPAL = 'logo_principal';
    const COLORES_INSTITUCIONALES = 'colores_institucionales';
    const MODO_MANTENIMIENTO = 'modo_mantenimiento';

    /**
     * Scope para configuraciones activas
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para configuraciones por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para configuraciones por clave
     */
    public function scopePorClave($query, $clave)
    {
        return $query->where('clave', $clave);
    }

    /**
     * Accessor para obtener valor casteado según el tipo
     */
    public function getValorCasteadoAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_NUMBER => (float) $this->valor,
            self::TIPO_BOOLEAN => filter_var($this->valor, FILTER_VALIDATE_BOOLEAN),
            self::TIPO_JSON => json_decode($this->valor, true),
            default => $this->valor
        };
    }

    /**
     * Mutator para validar formato según el tipo
     */
    public function setValorAttribute($value)
    {
        $this->attributes['valor'] = match ($this->tipo) {
            self::TIPO_JSON => is_string($value) ? $value : json_encode($value),
            self::TIPO_BOOLEAN => $value ? 'true' : 'false',
            default => (string) $value
        };
    }

    /**
     * Método estático para obtener una configuración
     */
    public static function obtener($clave, $valorDefecto = null)
    {
        $config = static::activo()->porClave($clave)->first();

        if (!$config) {
            return $valorDefecto;
        }

        return $config->valor_casteado;
    }

    /**
     * Método estático para establecer una configuración
     */
    public static function establecer($clave, $valor, $descripcion = null, $tipo = self::TIPO_STRING)
    {
        return static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor' => $valor,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
                'activo' => true,
            ]
        );
    }

    /**
     * Método estático para obtener toda la configuración del CICIT
     */
    public static function getConfiguracionCompleta()
    {
        return [
            'sitio' => [
                'nombre' => static::obtener(self::NOMBRE_SITIO, 'CICIT'),
                'nombre_corto' => static::obtener(self::NOMBRE_CORTO, 'CICIT'),
                'descripcion' => static::obtener(self::DESCRIPCION_SITIO, ''),
                'mision' => static::obtener(self::MISION, ''),
                'vision' => static::obtener(self::VISION, ''),
                'logo' => static::obtener(self::LOGO_PRINCIPAL, '/images/logo-cicit.png'),
            ],
            'contacto' => [
                'email' => static::obtener(self::EMAIL_CONTACTO, 'info@cicit.uagrm.edu.bo'),
                'telefono' => static::obtener(self::TELEFONO_CONTACTO, '+591 3 3336000'),
                'direccion' => static::obtener(self::DIRECCION, 'UAGRM, Santa Cruz - Bolivia'),
                'horario' => static::obtener(self::HORARIO_ATENCION, 'Lunes a Viernes: 8:00 - 18:00'),
            ],
            'sistema' => [
                'modo_mantenimiento' => static::obtener(self::MODO_MANTENIMIENTO, false),
                'colores' => static::obtener(self::COLORES_INSTITUCIONALES, []),
            ],
        ];
    }

    /**
     * Método estático para obtener configuración para layouts
     */
    public static function getConfiguracionLayout()
    {
        return [
            'nombre_sitio' => static::obtener(self::NOMBRE_SITIO, 'CICIT'),
            'logo_principal' => static::obtener(self::LOGO_PRINCIPAL, '/images/logo-cicit.png'),
            'colores_institucionales' => static::obtener(self::COLORES_INSTITUCIONALES, []),
        ];
    }

    /**
     * Método estático para verificar si está en modo mantenimiento
     */
    public static function estaEnMantenimiento()
    {
        return static::obtener(self::MODO_MANTENIMIENTO, false);
    }

    /**
     * Método estático para obtener información de contacto
     */
    public static function getInformacionContacto()
    {
        return [
            'email' => static::obtener(self::EMAIL_CONTACTO),
            'telefono' => static::obtener(self::TELEFONO_CONTACTO),
            'direccion' => static::obtener(self::DIRECCION),
            'horario' => static::obtener(self::HORARIO_ATENCION),
        ];
    }

    /**
     * Método estático para obtener configuraciones editables por el RESPONSABLE
     */
    public static function getConfiguracionesEditables()
    {
        $grupos = [
            'Información del Sitio' => [
                self::NOMBRE_SITIO,
                self::DESCRIPCION_SITIO,
                self::MISION,
                self::VISION,
            ],
            'Información de Contacto' => [
                self::EMAIL_CONTACTO,
                self::TELEFONO_CONTACTO,
                self::DIRECCION,
                self::HORARIO_ATENCION,
            ],
            'Configuración Visual' => [
                self::LOGO_PRINCIPAL,
                self::COLORES_INSTITUCIONALES,
            ],
            'Sistema' => [
                self::MODO_MANTENIMIENTO,
            ],
        ];

        $configuraciones = [];

        foreach ($grupos as $grupo => $claves) {
            $configuraciones[$grupo] = [];
            foreach ($claves as $clave) {
                $config = static::porClave($clave)->first();
                if ($config) {
                    $configuraciones[$grupo][] = [
                        'clave' => $config->clave,
                        'valor' => $config->valor_casteado,
                        'descripcion' => $config->descripcion,
                        'tipo' => $config->tipo,
                    ];
                }
            }
        }

        return $configuraciones;
    }

    /**
     * Método estático para actualizar múltiples configuraciones
     */
    public static function actualizarConfiguraciones($configuraciones)
    {
        foreach ($configuraciones as $clave => $valor) {
            $config = static::porClave($clave)->first();
            if ($config) {
                $config->update(['valor' => $valor]);
            }
        }

        return true;
    }

    /**
     * Método estático para exportar configuración
     */
    public static function exportarConfiguracion()
    {
        return static::activo()
            ->get()
            ->pluck('valor_casteado', 'clave')
            ->toArray();
    }

    /**
     * Método estático para restaurar configuración por defecto
     */
    public static function restaurarDefecto()
    {
        // Aquí podrías implementar la lógica para restaurar valores por defecto
        // basándote en los datos iniciales del seeder
        return true;
    }
}
