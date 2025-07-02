<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitaController extends Controller
{
    /**
     * Registrar una visita de página
     */
    public function registrar(Request $request)
    {
        $request->validate([
            'pagina' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Registrar la visita
        VisitaPagina::create([
            'usuario_id' => $userId,
            'pagina' => $request->pagina,
            'titulo_pagina' => $request->titulo,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'fecha_visita' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Obtener contador de visitas para una página específica
     */
    public function contador(Request $request, $pagina = null)
    {
        $pagina = $pagina ?? $request->get('pagina', '/');

        $count = VisitaPagina::where('pagina', $pagina)->count();
        $uniqueVisitors = VisitaPagina::where('pagina', $pagina)
            ->distinct('ip_address')
            ->count('ip_address');

        return response()->json([
            'count' => $count,
            'unique_visitors' => $uniqueVisitors,
            'pagina' => $pagina
        ]);
    }

    /**
     * Obtener estadísticas generales del sitio
     */
    public function estadisticas()
    {
        $stats = [
            'total_visits' => VisitaPagina::count(),
            'unique_visitors' => VisitaPagina::distinct('ip_address')->count('ip_address'),
            'total_users' => DB::table('usuario')->count(),
            'total_courses' => DB::table('curso')->count(),
            'total_registrations' => DB::table('inscripcion')->count(),
            'active_courses' => DB::table('curso')->where('activo', true)->count(),
            'visits_today' => VisitaPagina::whereDate('fecha_visita', today())->count(),
            'visits_this_week' => VisitaPagina::whereBetween('fecha_visita', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'visits_this_month' => VisitaPagina::whereMonth('fecha_visita', now()->month)
                ->whereYear('fecha_visita', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Obtener páginas más visitadas
     */
    public function paginasPopulares(Request $request)
    {
        $limit = $request->get('limit', 10);

        $populares = VisitaPagina::select('pagina', 'titulo_pagina')
            ->selectRaw('COUNT(*) as visitas')
            ->selectRaw('COUNT(DISTINCT ip_address) as visitantes_unicos')
            ->groupBy('pagina', 'titulo_pagina')
            ->orderBy('visitas', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($populares);
    }
}
