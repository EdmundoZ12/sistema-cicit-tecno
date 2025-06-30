<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BusquedaLog extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'BUSQUEDA_LOG';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'termino',
        'usuario_id',
        'resultados',
        'fecha_busqueda',
        'ip_address',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_busqueda' => 'datetime',
        'resultados' => 'integer',
    ];

    /**
     * Scope para búsquedas por término
     */
    public function scopePorTermino($query, $termino)
    {
        return $query->where('termino', 'ILIKE', "%{$termino}%");
    }

    /**
     * Scope para búsquedas por usuario
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para búsquedas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_busqueda', Carbon::today());
    }

    /**
     * Scope para búsquedas del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha_busqueda', Carbon::now()->month)
            ->whereYear('fecha_busqueda', Carbon::now()->year);
    }

    /**
     * Scope para búsquedas con resultados
     */
    public function scopeConResultados($query)
    {
        return $query->where('resultados', '>', 0);
    }

    /**
     * Scope para búsquedas sin resultados
     */
    public function scopeSinResultados($query)
    {
        return $query->where('resultados', 0);
    }

    /**
     * Scope para búsquedas recientes (últimos 7 días)
     */
    public function scopeRecientes($query)
    {
        return $query->where('fecha_busqueda', '>=', Carbon::now()->subDays(7));
    }

    /**
     * Relación: Una búsqueda puede pertenecer a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Accessor para verificar si tuvo resultados
     */
    public function getTuvoResultadosAttribute()
    {
        return $this->resultados > 0;
    }

    /**
     * Accessor para obtener término limpio (sin espacios extra)
     */
    public function getTerminoLimpioAttribute()
    {
        return trim(preg_replace('/\s+/', ' ', $this->termino));
    }

    /**
     * Accessor para verificar si es búsqueda reciente
     */
    public function getEsRecienteAttribute()
    {
        return $this->fecha_busqueda->diffInHours(Carbon::now()) <= 24;
    }

    /**
     * Método estático para registrar una búsqueda
     */
    public static function registrarBusqueda($termino, $resultados = 0, $request = null)
    {
        $request = $request ?? request();

        return static::create([
            'termino' => trim($termino),
            'usuario_id' => Auth::check() ? Auth::user()->id : null,
            'resultados' => $resultados,
            'fecha_busqueda' => now(),
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Método estático para obtener términos más buscados
     */
    public static function getTerminosMasBuscados($limite = 10, $dias = 30)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        return static::where('fecha_busqueda', '>=', $fechaInicio)
            ->selectRaw('LOWER(termino) as termino_lower, COUNT(*) as total_busquedas, AVG(resultados) as promedio_resultados')
            ->groupBy('termino_lower')
            ->orderByDesc('total_busquedas')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'termino' => $item->termino_lower,
                    'busquedas' => $item->total_busquedas,
                    'promedio_resultados' => round($item->promedio_resultados, 1),
                ];
            });
    }

    /**
     * Método estático para obtener búsquedas sin resultados más frecuentes
     */
    public static function getBusquedasSinResultados($limite = 10, $dias = 30)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        return static::sinResultados()
            ->where('fecha_busqueda', '>=', $fechaInicio)
            ->selectRaw('LOWER(termino) as termino_lower, COUNT(*) as total_busquedas')
            ->groupBy('termino_lower')
            ->orderByDesc('total_busquedas')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'termino' => $item->termino_lower,
                    'busquedas_fallidas' => $item->total_busquedas,
                ];
            });
    }

    /**
     * Método estático para obtener estadísticas de búsqueda
     */
    public static function getEstadisticasBusqueda($dias = 30)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        $totalBusquedas = static::where('fecha_busqueda', '>=', $fechaInicio)->count();
        $busquedasConResultados = static::conResultados()
            ->where('fecha_busqueda', '>=', $fechaInicio)
            ->count();
        $busquedasSinResultados = static::sinResultados()
            ->where('fecha_busqueda', '>=', $fechaInicio)
            ->count();
        $usuariosUnicos = static::where('fecha_busqueda', '>=', $fechaInicio)
            ->whereNotNull('usuario_id')
            ->distinct('usuario_id')
            ->count();
        $promedioResultados = static::where('fecha_busqueda', '>=', $fechaInicio)
            ->avg('resultados');

        return [
            'total_busquedas' => $totalBusquedas,
            'busquedas_exitosas' => $busquedasConResultados,
            'busquedas_fallidas' => $busquedasSinResultados,
            'tasa_exito' => $totalBusquedas > 0 ? round(($busquedasConResultados / $totalBusquedas) * 100, 2) : 0,
            'usuarios_unicos' => $usuariosUnicos,
            'promedio_resultados' => round($promedioResultados, 1),
            'busquedas_por_dia' => round($totalBusquedas / $dias, 1),
        ];
    }

    /**
     * Método estático para obtener tendencias de búsqueda
     */
    public static function getTendenciasBusqueda($dias = 7)
    {
        $fechaInicio = Carbon::now()->subDays($dias);

        return static::where('fecha_busqueda', '>=', $fechaInicio)
            ->selectRaw('DATE(fecha_busqueda) as fecha, COUNT(*) as total_busquedas')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(function ($item) {
                return [
                    'fecha' => Carbon::parse($item->fecha)->format('d/m'),
                    'busquedas' => $item->total_busquedas,
                ];
            });
    }

    /**
     * Método estático para obtener sugerencias basadas en búsquedas populares
     */
    public static function getSugerencias($termino, $limite = 5)
    {
        return static::conResultados()
            ->where('termino', 'ILIKE', "%{$termino}%")
            ->selectRaw('termino, COUNT(*) as popularidad')
            ->groupBy('termino')
            ->orderByDesc('popularidad')
            ->limit($limite)
            ->pluck('termino')
            ->toArray();
    }

    /**
     * Método para realizar búsqueda en el sistema CICIT
     */
    public static function buscarEnSistema($termino)
    {
        $resultados = [];
        $totalResultados = 0;

        // Buscar en cursos
        $cursos = Curso::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
            ->activo()
            ->get();

        if ($cursos->count() > 0) {
            $resultados['cursos'] = $cursos;
            $totalResultados += $cursos->count();
        }

        // Buscar en participantes
        $participantes = Participante::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('apellido', 'ILIKE', "%{$termino}%")
            ->orWhere('carnet', 'ILIKE', "%{$termino}%")
            ->activo()
            ->get();

        if ($participantes->count() > 0) {
            $resultados['participantes'] = $participantes;
            $totalResultados += $participantes->count();
        }

        // Buscar en usuarios (tutores)
        $usuarios = Usuario::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('apellido', 'ILIKE', "%{$termino}%")
            ->activo()
            ->get();

        if ($usuarios->count() > 0) {
            $resultados['usuarios'] = $usuarios;
            $totalResultados += $usuarios->count();
        }

        // Registrar la búsqueda
        static::registrarBusqueda($termino, $totalResultados);

        return [
            'termino' => $termino,
            'total_resultados' => $totalResultados,
            'resultados' => $resultados,
        ];
    }
}
