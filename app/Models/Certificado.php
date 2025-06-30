<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Certificado extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'CERTIFICADO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'inscripcion_id',
        'tipo',
        'codigo_verificacion',
        'fecha_emision',
        'url_pdf',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_emision' => 'date',
    ];

    /**
     * Tipos de certificado disponibles
     */
    const TIPO_PARTICIPACION = 'PARTICIPACION';
    const TIPO_APROBACION = 'APROBACION';
    const TIPO_MENCION_HONOR = 'MENCION_HONOR';

    /**
     * Scope para certificados por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para certificados de participación
     */
    public function scopeParticipacion($query)
    {
        return $query->where('tipo', self::TIPO_PARTICIPACION);
    }

    /**
     * Scope para certificados de aprobación
     */
    public function scopeAprobacion($query)
    {
        return $query->where('tipo', self::TIPO_APROBACION);
    }

    /**
     * Scope para certificados de mención honor
     */
    public function scopeMencionHonor($query)
    {
        return $query->where('tipo', self::TIPO_MENCION_HONOR);
    }

    /**
     * Scope para certificados por fecha de emisión
     */
    public function scopePorFechaEmision($query, $fecha)
    {
        return $query->whereDate('fecha_emision', $fecha);
    }

    /**
     * Scope para certificados del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha_emision', Carbon::now()->month)
            ->whereYear('fecha_emision', Carbon::now()->year);
    }

    /**
     * Relación: Un certificado pertenece a una inscripción
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
     * Accessor para obtener la descripción del tipo
     */
    public function getTipoDescripcionAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_PARTICIPACION => 'Certificado de Participación',
            self::TIPO_APROBACION => 'Certificado de Aprobación',
            self::TIPO_MENCION_HONOR => 'Certificado con Mención de Honor',
            default => 'Certificado'
        };
    }

    /**
     * Accessor para obtener el color del tipo (para UI)
     */
    public function getColorTipoAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_PARTICIPACION => 'info',     // Azul
            self::TIPO_APROBACION => 'success',     // Verde
            self::TIPO_MENCION_HONOR => 'warning',  // Dorado
            default => 'secondary'                  // Gris
        };
    }

    /**
     * Accessor para verificar si tiene PDF generado
     */
    public function getTienePdfAttribute()
    {
        return !empty($this->url_pdf);
    }

    /**
     * Accessor para verificar si es un certificado reciente (últimos 30 días)
     */
    public function getEsRecienteAttribute()
    {
        return $this->fecha_emision->diffInDays(Carbon::now()) <= 30;
    }

    /**
     * Método para verificar si es de participación
     */
    public function esParticipacion()
    {
        return $this->tipo === self::TIPO_PARTICIPACION;
    }

    /**
     * Método para verificar si es de aprobación
     */
    public function esAprobacion()
    {
        return $this->tipo === self::TIPO_APROBACION;
    }

    /**
     * Método para verificar si es mención de honor
     */
    public function esMencionHonor()
    {
        return $this->tipo === self::TIPO_MENCION_HONOR;
    }

    /**
     * Método para generar código de verificación único
     */
    public static function generarCodigoVerificacion()
    {
        do {
            $codigo = 'CICIT-' . Carbon::now()->year . '-' . strtoupper(Str::random(8));
        } while (static::where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    /**
     * Método para verificar la validez del certificado
     */
    public function esValido()
    {
        return !empty($this->codigo_verificacion) &&
            $this->inscripcion?->curso?->activo === true;
    }

    /**
     * Método para obtener la URL completa del PDF
     */
    public function getUrlCompletaPdf()
    {
        if (!$this->tiene_pdf) {
            return null;
        }

        return url($this->url_pdf);
    }

    /**
     * Método para obtener información completa del certificado
     */
    public function getInformacionCompleta()
    {
        return [
            'codigo' => $this->codigo_verificacion,
            'tipo' => $this->tipo_descripcion,
            'participante' => $this->participante?->nombre_completo,
            'curso' => $this->curso?->nombre,
            'fecha_emision' => $this->fecha_emision->format('d/m/Y'),
            'tutor' => $this->curso?->tutor?->nombre_completo,
            'gestion' => $this->curso?->gestion?->nombre,
            'duracion_horas' => $this->curso?->duracion_horas,
            'tiene_pdf' => $this->tiene_pdf,
            'url_pdf' => $this->getUrlCompletaPdf(),
        ];
    }

    /**
     * Boot method para generar código automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificado) {
            if (empty($certificado->codigo_verificacion)) {
                $certificado->codigo_verificacion = static::generarCodigoVerificacion();
            }
        });
    }
}
