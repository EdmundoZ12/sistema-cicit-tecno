<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\TipoParticipante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TipoParticipanteController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede gestionar tipos de participante
     */
    public function __construct()
    {
        // Middleware applied via route groups instead
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = TipoParticipante::query();

            // Aplicar filtros de búsqueda
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('codigo', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
                });
            }

            // Aplicar filtro de estado
            if ($request->activo !== null) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Obtener los resultados sin conteos por ahora
            $tiposParticipante = $query
                ->orderBy('codigo')
                ->paginate(10)
                ->withQueryString();

            $estadisticas = [
                'total' => TipoParticipante::count(),
                'activos' => TipoParticipante::where('activo', true)->count(),
                'conParticipantes' => 0,
            ];

            // Para peticiones AJAX del componente, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'tiposParticipante' => $tiposParticipante,
                    'estadisticas' => $estadisticas,
                    'filters' => $request->only(['search', 'activo'])
                ]);
            }

            return redirect()->route('responsable.dashboard');
        } catch (\Exception $e) {
            Log::error('Error en TipoParticipanteController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Error al cargar los tipos de participante',
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al cargar los tipos de participante');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Responsable/TiposParticipante/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:10', 'unique:TIPO_PARTICIPANTE,codigo'],
            'descripcion' => ['required', 'string', 'max:255'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe un tipo de participante con este código.',
            'codigo.max' => 'El código no puede tener más de 10 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres.',
        ]);

        // Normalizar código a mayúsculas
        $validated['codigo'] = strtoupper($validated['codigo']);

        $tipoParticipante = TipoParticipante::create([
            ...$validated,
            'activo' => true,
        ]);

        return redirect()->route('responsable.dashboard')
            ->with('success', 'Tipo de participante creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoParticipante $tipoParticipante): Response
    {
        $tipoParticipante->load(['participantes' => function ($query) {
            $query->activo()->latest()->take(10);
        }, 'preciosCursos.curso']);

        // Estadísticas del tipo de participante
        $estadisticas = [
            'total_participantes' => $tipoParticipante->participantes()->count(),
            'participantes_activos' => $tipoParticipante->participantes()->activo()->count(),
            'cursos_con_precio' => $tipoParticipante->preciosCursos()->count(),
            'inscripciones_totales' => $tipoParticipante->participantes()
                ->whereHas('inscripciones')
                ->count(),
            'promedio_precio_cursos' => $tipoParticipante->preciosCursos()
                ->where('activo', true)
                ->avg('precio') ?? 0,
        ];

        return Inertia::render('Responsable/TiposParticipante/Show', [
            'tipoParticipante' => $tipoParticipante,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TipoParticipante $tipoParticipante): Response
    {
        return Inertia::render('Responsable/TiposParticipante/Edit', [
            'tipoParticipante' => $tipoParticipante,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoParticipante $tipoParticipante)
    {
        // Solo validar y actualizar los campos que pueden cambiar
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
            'activo' => ['boolean'],
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres.',
        ]);

        $tipoParticipante->update($validated);

        return redirect()->route('responsable.dashboard')
            ->with('success', 'Tipo de participante actualizado exitosamente.');
    }

    /**
     * Desactivar tipo de participante (soft delete)
     */
    public function desactivar(Request $request, TipoParticipante $tipoParticipante)
    {
        // Verificar si tiene participantes activos
        $participantesActivos = $tipoParticipante->participantes()->activo()->count();

        if ($participantesActivos > 0) {
            $errorMessage = "No se puede desactivar el tipo de participante porque tiene {$participantesActivos} participante(s) activo(s).";
            return back()->with('error', $errorMessage);
        }

        // Verificar si tiene precios de cursos activos
        $preciosActivos = $tipoParticipante->preciosCursos()->where('activo', true)->count();

        if ($preciosActivos > 0) {
            $errorMessage = "No se puede desactivar el tipo de participante porque tiene precios activos en {$preciosActivos} curso(s).";
            return back()->with('error', $errorMessage);
        }

        $tipoParticipante->update(['activo' => false]);

        return back()->with('success', 'Tipo de participante desactivado exitosamente.');
    }

    /**
     * Reactivar tipo de participante
     */
    public function reactivar(Request $request, TipoParticipante $tipoParticipante)
    {
        $tipoParticipante->update(['activo' => true]);

        return back()->with('success', 'Tipo de participante reactivado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(Request $request, TipoParticipante $tipoParticipante)
    {
        if ($tipoParticipante->activo) {
            return $this->desactivar($request, $tipoParticipante);
        } else {
            return $this->reactivar($request, $tipoParticipante);
        }
    }

    /**
     * Obtener tipos de participante activos para selects
     */
    public function getTiposActivos()
    {
        $tipos = TipoParticipante::activo()
            ->select('id', 'codigo', 'descripcion')
            ->orderBy('codigo')
            ->get()
            ->map(function ($tipo) {
                return [
                    'id' => $tipo->id,
                    'codigo' => $tipo->codigo,
                    'descripcion' => $tipo->descripcion,
                    'nombre_completo' => $tipo->nombre_completo,
                ];
            });

        return response()->json($tipos);
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_tipos' => TipoParticipante::count(),
            'tipos_activos' => TipoParticipante::activo()->count(),
            'distribucion_participantes' => TipoParticipante::activo()
                ->withCount('participantes')
                ->get(['codigo', 'descripcion', 'participantes_count'])
                ->map(function ($tipo) {
                    return [
                        'tipo' => $tipo->codigo,
                        'descripcion' => $tipo->descripcion,
                        'participantes' => $tipo->participantes_count,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Eliminar tipo de participante
     */
    public function destroy(TipoParticipante $tipoParticipante)
    {
        // No permitir eliminar si tiene participantes asociados
        if ($tipoParticipante->participantes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: hay participantes asociados a este tipo.'
            ], 400);
        }
        $tipoParticipante->delete();
        return response()->json(['success' => true, 'message' => 'Tipo de participante eliminado exitosamente.']);
    }
}
