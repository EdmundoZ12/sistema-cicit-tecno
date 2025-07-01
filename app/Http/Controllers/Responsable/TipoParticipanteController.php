<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\TipoParticipante;
use Illuminate\Http\Request;
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
        $this->middleware(['auth', 'role:RESPONSABLE']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $tiposParticipante = TipoParticipante::query()
            ->when($request->search, function ($query, $search) {
                $query->where('codigo', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->withCount(['participantes', 'participantes as participantes_activos_count' => function ($query) {
                $query->where('activo', true);
            }])
            ->orderBy('codigo')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Responsable/TiposParticipante/Index', [
            'tiposParticipante' => $tiposParticipante,
            'filters' => $request->only(['search', 'activo']),
            'estadisticas' => [
                'total' => TipoParticipante::count(),
                'activos' => TipoParticipante::activo()->count(),
                'con_participantes' => TipoParticipante::has('participantes')->count(),
            ]
        ]);
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

        TipoParticipante::create([
            ...$validated,
            'activo' => true,
        ]);

        return redirect()->route('responsable.tipos-participante.index')
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
        $validated = $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:10',
                Rule::unique('TIPO_PARTICIPANTE', 'codigo')->ignore($tipoParticipante->id)
            ],
            'descripcion' => ['required', 'string', 'max:255'],
            'activo' => ['boolean'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Ya existe otro tipo de participante con este código.',
            'codigo.max' => 'El código no puede tener más de 10 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 255 caracteres.',
        ]);

        // Normalizar código a mayúsculas
        $validated['codigo'] = strtoupper($validated['codigo']);

        $tipoParticipante->update($validated);

        return redirect()->route('responsable.tipos-participante.index')
            ->with('success', 'Tipo de participante actualizado exitosamente.');
    }

    /**
     * Desactivar tipo de participante (soft delete)
     */
    public function desactivar(TipoParticipante $tipoParticipante)
    {
        // Verificar si tiene participantes activos
        $participantesActivos = $tipoParticipante->participantes()->activo()->count();

        if ($participantesActivos > 0) {
            return back()->with(
                'error',
                "No se puede desactivar el tipo de participante porque tiene {$participantesActivos} participante(s) activo(s)."
            );
        }

        // Verificar si tiene precios de cursos activos
        $preciosActivos = $tipoParticipante->preciosCursos()->where('activo', true)->count();

        if ($preciosActivos > 0) {
            return back()->with(
                'error',
                "No se puede desactivar el tipo de participante porque tiene precios activos en {$preciosActivos} curso(s)."
            );
        }

        $tipoParticipante->update(['activo' => false]);

        return back()->with('success', 'Tipo de participante desactivado exitosamente.');
    }

    /**
     * Reactivar tipo de participante
     */
    public function reactivar(TipoParticipante $tipoParticipante)
    {
        $tipoParticipante->update(['activo' => true]);

        return back()->with('success', 'Tipo de participante reactivado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(TipoParticipante $tipoParticipante)
    {
        if ($tipoParticipante->activo) {
            return $this->desactivar($tipoParticipante);
        } else {
            return $this->reactivar($tipoParticipante);
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
}
