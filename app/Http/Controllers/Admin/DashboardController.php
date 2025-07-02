<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard administrativo principal
     */
    public function index(): Response
    {
        // Obtener estadísticas generales
        $stats = [
            'preinscripciones_pendientes' => Preinscripcion::where('estado', 'PENDIENTE')->count(),
            'inscripciones_activas' => Inscripcion::where('estado', 'INSCRITO')->count(),
            'cursos_activos' => Curso::where('activo', true)
                ->whereRaw('fecha_inicio >= ?', [now()->toDateString()])
                ->count(),
            'ingresos_mes' => Pago::whereMonth('fecha_pago', now()->month)
                ->whereYear('fecha_pago', now()->year)
                ->sum('monto')
        ];

        // Obtener preinscripciones recientes (últimas 5)
        $preinscripciones_recientes = Preinscripcion::with([
                'participante',
                'curso'
            ])
            ->orderBy('fecha_preinscripcion', 'desc')
            ->limit(5)
            ->get();

        // Obtener pagos recientes (últimos 5)
        $pagos_recientes = Pago::with([
                'preinscripcion.participante',
                'preinscripcion.curso'
            ])
            ->orderBy('fecha_pago', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'preinscripciones_recientes' => $preinscripciones_recientes,
            'pagos_recientes' => $pagos_recientes,
        ]);
    }
}
