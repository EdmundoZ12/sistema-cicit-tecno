<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TemaConfiguracion extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'TEMA_CONFIGURACION';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'color_primario',
        'color_secundario',
        'color_fondo',
        'color_texto',
        'tamano_fuente_base',
        'alto_contraste',
        'target_edad',
        'modo_oscuro',
        'activo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'alto_contraste' => 'boolean',
        'modo_oscuro' => 'boolean',
        'activo' => 'boolean',
        'tamano_fuente_base' => 'integer',
    ];

    /**
     * Targets de edad disponibles
     */
    const TARGET_NINOS = 'ninos';
    const TARGET_JOVENES = 'jovenes';
    const TARGET_ADULTOS = 'adultos';

    /**
     * Scope para temas activos
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para temas por target de edad
     */
    public function scopePorTargetEdad($query, $target)
    {
        return $query->where('target_edad', $target);
    }

    /**
     * Scope para temas con modo oscuro
     */
    public function scopeModoOscuro($query)
    {
        return $query->where('modo_oscuro', true);
    }

    /**
     * Scope para temas con alto contraste
     */
    public function scopeAltoContraste($query)
    {
        return $query->where('alto_contraste', true);
    }

    /**
     * Relación: Un tema puede ser usado por muchos usuarios
     */
    public function configuracionesUsuario()
    {
        return $this->hasMany(ConfiguracionUsuario::class, 'tema_id');
    }

    /**
     * Accessor para verificar si es tema para niños
     */
    public function getEsParaNinosAttribute()
    {
        return $this->target_edad === self::TARGET_NINOS;
    }

    /**
     * Accessor para verificar si es tema para jóvenes
     */
    public function getEsParaJovenesAttribute()
    {
        return $this->target_edad === self::TARGET_JOVENES;
    }

    /**
     * Accessor para verificar si es tema para adultos
     */
    public function getEsParaAdultosAttribute()
    {
        return $this->target_edad === self::TARGET_ADULTOS;
    }

    /**
     * Accessor para obtener variables CSS
     */
    public function getVariablesCssAttribute()
    {
        return [
            '--color-primario' => $this->color_primario,
            '--color-secundario' => $this->color_secundario,
            '--color-fondo' => $this->color_fondo,
            '--color-texto' => $this->color_texto,
            '--tamano-fuente-base' => $this->tamano_fuente_base . 'px',
        ];
    }

    /**
     * Accessor para generar CSS completo del tema
     */
    public function getCssCompletoAttribute()
    {
        $variables = [];
        foreach ($this->variables_css as $variable => $valor) {
            $variables[] = "  {$variable}: {$valor};";
        }

        return ":root {\n" . implode("\n", $variables) . "\n}";
    }

    /**
     * Método para obtener tema apropiado según la hora
     */
    public static function getTemaSegunHora($targetEdad = null)
    {
        $hora = Carbon::now()->hour;
        $esModoOscuro = $hora >= 18 || $hora <= 6; // 6 PM a 6 AM

        $query = static::activo();

        if ($targetEdad) {
            $query->porTargetEdad($targetEdad);
        }

        if ($esModoOscuro) {
            $tema = $query->modoOscuro()->first();
            if ($tema) {
                return $tema;
            }
        }

        // Si no hay tema oscuro o no es horario nocturno, buscar tema claro
        return $query->where('modo_oscuro', false)->first();
    }

    /**
     * Método para obtener tema por defecto
     */
    public static function getTemaDefecto()
    {
        return static::activo()
            ->porTargetEdad(self::TARGET_ADULTOS)
            ->where('modo_oscuro', false)
            ->first();
    }

    /**
     * Método para validar formato de color hexadecimal
     */
    public static function validarColorHex($color)
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color);
    }

    /**
     * Mutator para validar color primario
     */
    public function setColorPrimarioAttribute($value)
    {
        if (static::validarColorHex($value)) {
            $this->attributes['color_primario'] = strtoupper($value);
        }
    }

    /**
     * Mutator para validar color secundario
     */
    public function setColorSecundarioAttribute($value)
    {
        if (static::validarColorHex($value)) {
            $this->attributes['color_secundario'] = strtoupper($value);
        }
    }

    /**
     * Mutator para validar color de fondo
     */
    public function setColorFondoAttribute($value)
    {
        if (static::validarColorHex($value)) {
            $this->attributes['color_fondo'] = strtoupper($value);
        }
    }

    /**
     * Mutator para validar color de texto
     */
    public function setColorTextoAttribute($value)
    {
        if (static::validarColorHex($value)) {
            $this->attributes['color_texto'] = strtoupper($value);
        }
    }

    /**
     * Método para obtener información completa del tema
     */
    public function getInformacionCompleta()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'target_edad' => $this->target_edad,
            'modo_oscuro' => $this->modo_oscuro,
            'alto_contraste' => $this->alto_contraste,
            'variables_css' => $this->variables_css,
            'css_completo' => $this->css_completo,
        ];
    }
}
