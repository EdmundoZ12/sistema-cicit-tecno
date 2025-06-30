<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pago extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'PAGO';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'preinscripcion_id',
        'fecha_pago',
        'monto',
        'recibo',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    /**
     * Scope para pagos por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_pago', $fecha);
    }

    /**
     * Scope para pagos del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha_pago', Carbon::now()->month)
            ->whereYear('fecha_pago', Carbon::now()->year);
    }

    /**
     * Scope para pagos por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_pago', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para pagos por monto mínimo
     */
    public function scopeMontoMinimo($query, $monto)
    {
        return $query->where('monto', '>=', $monto);
    }

    /**
     * Relación: Un pago pertenece a una preinscripción
     */
    public function preinscripcion()
    {
        return $this->belongsTo(Preinscripcion::class, 'preinscripcion_id');
    }

    /**
     * Accessor para obtener el monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return 'Bs. ' . number_format($this->monto, 2);
    }

    /**
     * Accessor para obtener información del participante via preinscripción
     */
    public function getParticipanteAttribute()
    {
        return $this->preinscripcion?->participante;
    }

    /**
     * Accessor para obtener información del curso via preinscripción
     */
    public function getCursoAttribute()
    {
        return $this->preinscripcion?->curso;
    }

    /**
     * Accessor para obtener días desde el pago
     */
    public function getDiasDesdepagoAttribute()
    {
        return $this->fecha_pago->diffInDays(Carbon::now());
    }

    /**
     * Mutator para asegurar que el monto sea positivo
     */
    public function setMontoAttribute($value)
    {
        $this->attributes['monto'] = max(0, (float) $value);
    }

    /**
     * Mutator para formato consistente del recibo
     */
    public function setReciboAttribute($value)
    {
        $this->attributes['recibo'] = strtoupper(trim($value));
    }

    /**
     * Método para verificar si el pago es reciente (últimos 7 días)
     */
    public function esReciente()
    {
        return $this->fecha_pago->diffInDays(Carbon::now()) <= 7;
    }

    /**
     * Método para verificar si el monto coincide con el precio del curso
     */
    public function montoCoincideConPrecio()
    {
        $precioEsperado = $this->preinscripcion?->getPrecioAplicable();
        return $precioEsperado && abs($this->monto - $precioEsperado) < 0.01;
    }

    /**
     * Método para obtener información completa del pago
     */
    public function getInformacionCompleta()
    {
        return [
            'recibo' => $this->recibo,
            'monto' => $this->monto_formateado,
            'fecha' => $this->fecha_pago->format('d/m/Y H:i'),
            'participante' => $this->participante?->nombre_completo,
            'curso' => $this->curso?->nombre,
        ];
    }
}
