<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConfiguracionUsuario extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'CONFIGURACION_USUARIO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'usuario_id',
        'tema_id',
        'tamano_fuente',
        'alto_contraste',
        'modo_automatico',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'alto_contraste' => 'boolean',
        'modo_automatico' => 'boolean',
        'tamano_fuente' => 'integer',
    ];

    /**
     * Tamaños de fuente disponibles para accesibilidad
     */
    const TAMANO_PEQUENO = 14;
    const TAMANO_NORMAL = 16;
    const TAMANO_GRANDE = 18;
    const TAMANO_MUY_GRANDE = 20;

    /**
     * Relación: Una configuración pertenece a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación: Una configuración puede tener un tema
     */
    public function tema()
    {
        return $this->belongsTo(TemaConfiguracion::class, 'tema_id');
    }

    /**
     * Accessor para obtener el tema efectivo (considerando modo automático)
     */
    public function getTemaEfectivoAttribute()
    {
        if ($this->modo_automatico) {
            // Modo automático: seleccionar tema según la hora
            $targetEdad = $this->tema?->target_edad ?? TemaConfiguracion::TARGET_ADULTOS;
            return TemaConfiguracion::getTemaSegunHora($targetEdad);
        }

        // Modo manual: usar tema seleccionado o tema por defecto
        return $this->tema ?? TemaConfiguracion::getTemaDefecto();
    }

    /**
     * Accessor para verificar si debe usar modo oscuro
     */
    public function getDebeUsarModoOscuroAttribute()
    {
        if (!$this->modo_automatico) {
            return $this->tema?->modo_oscuro ?? false;
        }

        // Modo automático: verificar horario
        $hora = Carbon::now()->hour;
        return $hora >= 18 || $hora <= 6; // 6 PM a 6 AM
    }

    /**
     * Accessor para obtener variables CSS personalizadas
     */
    public function getVariablesCssPersonalizadasAttribute()
    {
        $temaEfectivo = $this->tema_efectivo;
        $variables = $temaEfectivo->variables_css;

        // Personalizar tamaño de fuente
        $variables['--tamano-fuente-base'] = $this->tamano_fuente . 'px';

        // Aplicar alto contraste si está activado
        if ($this->alto_contraste) {
            $variables['--color-fondo'] = '#FFFFFF';
            $variables['--color-texto'] = '#000000';
            $variables['--color-primario'] = '#0000FF';
            $variables['--color-secundario'] = '#FF0000';
        }

        return $variables;
    }

    /**
     * Accessor para generar CSS personalizado completo
     */
    public function getCssPersonalizadoAttribute()
    {
        $variables = [];
        foreach ($this->variables_css_personalizadas as $variable => $valor) {
            $variables[] = "  {$variable}: {$valor};";
        }

        return ":root {\n" . implode("\n", $variables) . "\n}";
    }

    /**
     * Método para verificar si usa tamaño de fuente grande (accesibilidad)
     */
    public function usaFuenteGrande()
    {
        return $this->tamano_fuente >= self::TAMANO_GRANDE;
    }

    /**
     * Método para verificar si tiene configuraciones de accesibilidad activas
     */
    public function tieneAccesibilidadActiva()
    {
        return $this->alto_contraste || $this->usaFuenteGrande();
    }

    /**
     * Método para obtener tamaños de fuente disponibles
     */
    public static function getTamanosDisponibles()
    {
        return [
            self::TAMANO_PEQUENO => 'Pequeño (14px)',
            self::TAMANO_NORMAL => 'Normal (16px)',
            self::TAMANO_GRANDE => 'Grande (18px)',
            self::TAMANO_MUY_GRANDE => 'Muy Grande (20px)',
        ];
    }

    /**
     * Método para crear configuración por defecto para un usuario
     */
    public static function crearConfiguracionDefecto($usuarioId)
    {
        return static::create([
            'usuario_id' => $usuarioId,
            'tema_id' => TemaConfiguracion::getTemaDefecto()?->id,
            'tamano_fuente' => self::TAMANO_NORMAL,
            'alto_contraste' => false,
            'modo_automatico' => true,
        ]);
    }

    /**
     * Método para actualizar configuración
     */
    public function actualizarConfiguracion($datos)
    {
        $this->update([
            'tema_id' => $datos['tema_id'] ?? $this->tema_id,
            'tamano_fuente' => $datos['tamano_fuente'] ?? $this->tamano_fuente,
            'alto_contraste' => $datos['alto_contraste'] ?? $this->alto_contraste,
            'modo_automatico' => $datos['modo_automatico'] ?? $this->modo_automatico,
        ]);

        return $this;
    }

    /**
     * Método para obtener información completa de la configuración
     */
    public function getInformacionCompleta()
    {
        return [
            'tema_efectivo' => $this->tema_efectivo?->getInformacionCompleta(),
            'tamano_fuente' => $this->tamano_fuente,
            'alto_contraste' => $this->alto_contraste,
            'modo_automatico' => $this->modo_automatico,
            'debe_usar_modo_oscuro' => $this->debe_usar_modo_oscuro,
            'css_personalizado' => $this->css_personalizado,
            'tiene_accesibilidad_activa' => $this->tieneAccesibilidadActiva(),
        ];
    }

    /**
     * Boot method para crear configuración automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        // Cuando se crea un usuario, crear su configuración por defecto
        Usuario::created(function ($usuario) {
            static::crearConfiguracionDefecto($usuario->id);
        });
    }
}
