<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Estadistica extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'ESTADISTICA';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'tipo',
        'valor',
        'fecha',
        'descripcion',
        'metadata',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2',
        'metadata' => 'json',
    ];

    /**
     * Tipos de estadísticas del negocio CICIT
     */
    const TIPO_TOTAL_USUARIOS = 'total_usuarios';
    const TIPO_TOTAL_PARTICIPANTES = 'total_participantes';
    const TIPO_TOTAL_CURSOS = 'total_cursos';
    const TIPO_CURSOS_ACTIVOS = 'cursos_activos';
    const TIPO_INSCRIPCIONES_MES = 'inscripciones_mes';
    const TIPO_CERTIFICADOS_EMITIDOS = 'certificados_emitidos';
    const TIPO_INGRESOS_MES = 'ingresos_mes';
    const TIPO_INGRESOS_TOTALES = 'ingresos_totales';
    const TIPO_PARTICIPANTES_FICCT = 'participantes_ficct';
    const TIPO_PARTICIPANTES_UAGRM = 'participantes_uagrm';
    const TIPO_PARTICIPANTES_EXTERNOS = 'participantes_externos';

    /**
     * Scope para estadísticas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para estadísticas por fecha
     */
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para estadísticas del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha', Carbon::now()->month)
            ->whereYear('fecha', Carbon::now()->year);
    }

    /**
     * Scope para estadísticas entre fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para obtener la estadística más reciente de un tipo
     */
    public function scopeUltimaDelTipo($query, $tipo)
    {
        return $query->porTipo($tipo)->latest('fecha');
    }

    /**
     * Accessor para obtener valor formateado según el tipo
     */
    public function getValorFormateadoAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_INGRESOS_MES,
            self::TIPO_INGRESOS_TOTALES => 'Bs. ' . number_format($this->valor, 2),

            self::TIPO_INSCRIPCIONES_MES => number_format($this->valor, 0) . ' inscripciones',

            self::TIPO_CERTIFICADOS_EMITIDOS => number_format($this->valor, 0) . ' certificados',

            default => number_format($this->valor, 0)
        };
    }

    /**
     * Accessor para obtener descripción del tipo
     */
    public function getTipoDescripcionAttribute()
    {
        return match ($this->tipo) {
            self::TIPO_TOTAL_USUARIOS => 'Total de Usuarios',
            self::TIPO_TOTAL_PARTICIPANTES => 'Total de Participantes',
            self::TIPO_TOTAL_CURSOS => 'Total de Cursos',
            self::TIPO_CURSOS_ACTIVOS => 'Cursos Activos',
            self::TIPO_INSCRIPCIONES_MES => 'Inscripciones del Mes',
            self::TIPO_CERTIFICADOS_EMITIDOS => 'Certificados Emitidos',
            self::TIPO_INGRESOS_MES => 'Ingresos del Mes',
            self::TIPO_INGRESOS_TOTALES => 'Ingresos Totales',
            self::TIPO_PARTICIPANTES_FICCT => 'Participantes FICCT',
            self::TIPO_PARTICIPANTES_UAGRM => 'Participantes UAGRM',
            self::TIPO_PARTICIPANTES_EXTERNOS => 'Participantes Externos',
            default => $this->tipo
        };
    }

    /**
     * Método estático para actualizar una estadística
     */
    public static function actualizarEstadistica($tipo, $valor, $descripcion = null, $metadata = null)
    {
        return static::updateOrCreate(
            [
                'tipo' => $tipo,
                'fecha' => Carbon::today()
            ],
            [
                'valor' => $valor,
                'descripcion' => $descripcion,
                'metadata' => $metadata
            ]
        );
    }

    /**
     * Método estático para obtener valor actual de una estadística
     */
    public static function obtenerValor($tipo)
    {
        return static::ultimaDelTipo($tipo)->first()?->valor ?? 0;
    }

    /**
     * Método estático para calcular y actualizar todas las estadísticas
     */
    public static function calcularEstadisticasActuales()
    {
        $hoy = Carbon::today();
        $mesActual = Carbon::now();

        // Estadísticas generales
        static::actualizarEstadistica(
            self::TIPO_TOTAL_USUARIOS,
            Usuario::count(),
            'Total de usuarios registrados en el sistema'
        );

        static::actualizarEstadistica(
            self::TIPO_TOTAL_PARTICIPANTES,
            Participante::activo()->count(),
            'Total de participantes activos'
        );

        static::actualizarEstadistica(
            self::TIPO_TOTAL_CURSOS,
            Curso::count(),
            'Total de cursos creados'
        );

        static::actualizarEstadistica(
            self::TIPO_CURSOS_ACTIVOS,
            Curso::activo()->enProgreso()->count(),
            'Cursos actualmente en progreso'
        );

        // Estadísticas del mes
        $inscripcionesMes = Inscripcion::whereMonth('created_at', $mesActual->month)
            ->whereYear('created_at', $mesActual->year)
            ->count();

        static::actualizarEstadistica(
            self::TIPO_INSCRIPCIONES_MES,
            $inscripcionesMes,
            "Inscripciones realizadas en {$mesActual->format('F Y')}"
        );

        $certificadosMes = Certificado::whereMonth('fecha_emision', $mesActual->month)
            ->whereYear('fecha_emision', $mesActual->year)
            ->count();

        static::actualizarEstadistica(
            self::TIPO_CERTIFICADOS_EMITIDOS,
            $certificadosMes,
            "Certificados emitidos en {$mesActual->format('F Y')}"
        );

        // Ingresos
        $ingresosMes = Pago::whereMonth('fecha_pago', $mesActual->month)
            ->whereYear('fecha_pago', $mesActual->year)
            ->sum('monto');

        static::actualizarEstadistica(
            self::TIPO_INGRESOS_MES,
            $ingresosMes,
            "Ingresos generados en {$mesActual->format('F Y')}"
        );

        static::actualizarEstadistica(
            self::TIPO_INGRESOS_TOTALES,
            Pago::sum('monto'),
            'Ingresos totales acumulados'
        );

        // Participantes por tipo
        $participantesFicct = Participante::whereHas('tipoParticipante', function ($q) {
            $q->where('codigo', 'EST_FICCT');
        })->count();

        static::actualizarEstadistica(
            self::TIPO_PARTICIPANTES_FICCT,
            $participantesFicct,
            'Estudiantes de la Facultad FICCT'
        );

        $participantesUagrm = Participante::whereHas('tipoParticipante', function ($q) {
            $q->where('codigo', 'EST_UAGRM');
        })->count();

        static::actualizarEstadistica(
            self::TIPO_PARTICIPANTES_UAGRM,
            $participantesUagrm,
            'Estudiantes de la UAGRM'
        );

        $participantesExternos = Participante::whereHas('tipoParticipante', function ($q) {
            $q->where('codigo', 'PARTICULAR');
        })->count();

        static::actualizarEstadistica(
            self::TIPO_PARTICIPANTES_EXTERNOS,
            $participantesExternos,
            'Participantes externos'
        );

        return true;
    }

    /**
     * Método estático para obtener estadísticas para dashboard
     */
    public static function getEstadisticasDashboard()
    {
        return [
            'usuarios_totales' => static::obtenerValor(self::TIPO_TOTAL_USUARIOS),
            'participantes_totales' => static::obtenerValor(self::TIPO_TOTAL_PARTICIPANTES),
            'cursos_activos' => static::obtenerValor(self::TIPO_CURSOS_ACTIVOS),
            'inscripciones_mes' => static::obtenerValor(self::TIPO_INSCRIPCIONES_MES),
            'certificados_mes' => static::obtenerValor(self::TIPO_CERTIFICADOS_EMITIDOS),
            'ingresos_mes' => static::obtenerValor(self::TIPO_INGRESOS_MES),
            'ingresos_totales' => static::obtenerValor(self::TIPO_INGRESOS_TOTALES),
        ];
    }

    /**
     * Método estático para obtener evolución de una estadística
     */
    public static function getEvolucion($tipo, $dias = 30)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        return static::porTipo($tipo)
            ->entreFechas($fechaInicio, Carbon::now())
            ->orderBy('fecha')
            ->get()
            ->map(function ($stat) {
                return [
                    'fecha' => $stat->fecha->format('Y-m-d'),
                    'valor' => $stat->valor,
                ];
            });
    }
}
