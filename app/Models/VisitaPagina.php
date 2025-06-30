<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisitaPagina extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'VISITA_PAGINA';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'pagina',
        'ip_address',
        'usuario_id',
        'fecha_visita',
        'user_agent',
        'session_id',
    ];

    /**
     * Campos que deben ser casteados a tipos nativos
     */
    protected $casts = [
        'fecha_visita' => 'datetime',
    ];

    /**
     * Scope para visitas por página
     */
    public function scopePorPagina($query, $pagina)
    {
        return $query->where('pagina', $pagina);
    }

    /**
     * Scope para visitas por usuario
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para visitas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_visita', Carbon::today());
    }

    /**
     * Scope para visitas del mes actual
     */
    public function scopeDelMesActual($query)
    {
        return $query->whereMonth('fecha_visita', Carbon::now()->month)
            ->whereYear('fecha_visita', Carbon::now()->year);
    }

    /**
     * Scope para visitas únicas (por IP)
     */
    public function scopeUnicas($query)
    {
        return $query->select('pagina', 'ip_address')
            ->distinct();
    }

    /**
     * Scope para visitas entre fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_visita', [$fechaInicio, $fechaFin]);
    }

    /**
     * Relación: Una visita puede pertenecer a un usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Accessor para verificar si es visita de hoy
     */
    public function getEsVisitaHoyAttribute()
    {
        return $this->fecha_visita->isToday();
    }

    /**
     * Accessor para obtener nombre de la página limpio
     */
    public function getNombrePaginaAttribute()
    {
        // Convertir rutas en nombres amigables
        $nombres = [
            '/' => 'Inicio',
            '/dashboard' => 'Dashboard',
            '/cursos' => 'Cursos',
            '/participantes' => 'Participantes',
            '/inscripciones' => 'Inscripciones',
            '/usuarios' => 'Usuarios',
            '/reportes' => 'Reportes',
            '/configuracion' => 'Configuración',
        ];

        return $nombres[$this->pagina] ?? ucfirst(str_replace(['/', '-', '_'], [' ', ' ', ' '], $this->pagina));
    }

    /**
     * Método estático para registrar una visita
     */
    public static function registrarVisita($pagina, $request = null)
    {
        $request = $request ?? request();

        return static::create([
            'pagina' => $pagina,
            'ip_address' => $request->ip(),
            'usuario_id' => Auth::check() ? Auth::user()->id : null,
            'fecha_visita' => now(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Método estático para contar visitas totales de una página
     */
    public static function contarVisitasPagina($pagina)
    {
        return static::porPagina($pagina)->count();
    }

    /**
     * Método estático para contar visitas únicas de una página
     */
    public static function contarVisitasUnicas($pagina)
    {
        return static::porPagina($pagina)
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Método estático para obtener estadísticas de una página
     */
    public static function getEstadisticasPagina($pagina)
    {
        $visitasHoy = static::porPagina($pagina)->hoy()->count();
        $visitasMes = static::porPagina($pagina)->delMesActual()->count();
        $visitasTotales = static::contarVisitasPagina($pagina);
        $visitasUnicas = static::contarVisitasUnicas($pagina);

        return [
            'pagina' => $pagina,
            'visitas_hoy' => $visitasHoy,
            'visitas_mes' => $visitasMes,
            'visitas_totales' => $visitasTotales,
            'visitas_unicas' => $visitasUnicas,
            'ultima_visita' => static::porPagina($pagina)
                ->latest('fecha_visita')
                ->first()?->fecha_visita,
        ];
    }

    /**
     * Método estático para obtener páginas más visitadas
     */
    public static function getPaginasMasVisitadas($limite = 10)
    {
        return static::selectRaw('pagina, COUNT(*) as total_visitas')
            ->groupBy('pagina')
            ->orderByDesc('total_visitas')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'pagina' => $item->pagina,
                    'nombre' => (new static(['pagina' => $item->pagina]))->nombre_pagina,
                    'visitas' => $item->total_visitas,
                ];
            });
    }

    /**
     * Método estático para obtener estadísticas generales
     */
    public static function getEstadisticasGenerales()
    {
        $visitasHoy = static::hoy()->count();
        $visitasMes = static::delMesActual()->count();
        $visitasTotales = static::count();
        $paginasUnicas = static::distinct('pagina')->count('pagina');
        $usuariosActivos = static::delMesActual()
            ->whereNotNull('usuario_id')
            ->distinct('usuario_id')
            ->count('usuario_id');

        return [
            'visitas_hoy' => $visitasHoy,
            'visitas_mes' => $visitasMes,
            'visitas_totales' => $visitasTotales,
            'paginas_unicas' => $paginasUnicas,
            'usuarios_activos_mes' => $usuariosActivos,
            'promedio_visitas_diarias' => $visitasMes / Carbon::now()->day,
        ];
    }

    /**
     * Método para obtener texto para mostrar en el pie de página
     */
    public static function getTextoContador($pagina)
    {
        $visitas = static::contarVisitasPagina($pagina);

        if ($visitas == 1) {
            return "Esta página ha sido visitada 1 vez";
        }

        return "Esta página ha sido visitada {$visitas} veces";
    }
}
