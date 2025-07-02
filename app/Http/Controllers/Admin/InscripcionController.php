<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InscripcionController extends Controller
{
    /**
     * Mostrar listado de inscripciones
     */
    public function index(Request $request): Response
    {
        $query = Inscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor',
            'preinscripcion'
        ]);

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('participante', function ($pq) use ($search) {
                    $pq->where('nombre', 'ilike', "%{$search}%")
                      ->orWhere('apellido', 'ilike', "%{$search}%")
                      ->orWhere('carnet', 'like', "%{$search}%");
                })
                ->orWhereHas('curso', function ($cq) use ($search) {
                    $cq->where('nombre', 'ilike', "%{$search}%");
                });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        // Obtener resultados paginados
        $inscripciones = $query->orderBy('fecha_inscripcion', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Estadísticas
        $stats = [
            'total_inscripciones' => Inscripcion::count(),
            'inscripciones_activas' => Inscripcion::where('estado', 'INSCRITO')->count(),
            'inscripciones_hoy' => Inscripcion::whereDate('fecha_inscripcion', today())->count(),
        ];

        return Inertia::render('Admin/Inscripciones/Index', [
            'inscripciones' => $inscripciones,
            'stats' => $stats,
            'filters' => $request->only(['search', 'estado', 'curso_id'])
        ]);
    }
}
