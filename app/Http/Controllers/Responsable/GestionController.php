<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Gestion;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class GestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Gestion::query();

            // Aplicar filtros de búsqueda
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
                });
            }

            // Aplicar filtro de estado activo
            if ($request->activo !== null) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Aplicar filtro de estado temporal
            if ($request->estado) {
                switch ($request->estado) {
                    case 'actual':
                        $query->actual();
                        break;
                    case 'futura':
                        $query->futura();
                        break;
                    case 'pasada':
                        $query->pasada();
                        break;
                }
            }

            // Obtener los resultados
            $gestiones = $query
                ->withCount(['cursos', 'cursos as cursos_activos_count' => function ($query) {
                    $query->where('activo', true);
                }])
                ->orderBy('fecha_inicio', 'desc')
                ->paginate(10)
                ->withQueryString();

            // Agregar estado calculado a cada gestión
            $gestiones->getCollection()->transform(function ($gestion) {
                $hoy = Carbon::now();
                if ($gestion->fecha_fin < $hoy) {
                    $gestion->estado_calculado = 'Finalizada';
                    $gestion->color_estado = 'secondary';
                } elseif ($gestion->fecha_inicio > $hoy) {
                    $gestion->estado_calculado = 'Próxima';
                    $gestion->color_estado = 'info';
                } else {
                    $gestion->estado_calculado = 'En Curso';
                    $gestion->color_estado = 'success';
                }
                return $gestion;
            });

            $estadisticas = [
                'total' => Gestion::count(),
                'activas' => Gestion::activo()->count(),
                'en_curso' => Gestion::actual()->count(),
                'futuras' => Gestion::futura()->count(),
                'finalizadas' => Gestion::pasada()->count(),
            ];

            // Para peticiones AJAX del componente, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'gestiones' => $gestiones,
                    'estadisticas' => $estadisticas,
                    'filters' => $request->only(['search', 'activo', 'estado'])
                ]);
            }

            return redirect()->route('responsable.dashboard');
        } catch (\Exception $e) {
            Log::error('Error en GestionController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Error al cargar las gestiones',
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Error al cargar las gestiones');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Sugerir fechas para nueva gestión
        $fechaSugerida = Carbon::now()->addMonths(1)->startOfMonth();

        return Inertia::render('Gestiones/Create', [
            'fecha_inicio_sugerida' => $fechaSugerida->format('Y-m-d'),
            'fecha_fin_sugerida' => $fechaSugerida->copy()->addMonths(6)->endOfMonth()->format('Y-m-d'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'fecha_inicio' => ['required', 'date', 'before:fecha_fin'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
        ], [
            'nombre.required' => 'El nombre de la gestión es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.before' => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        // Validar que no se solapen fechas con otras gestiones activas
        $solapamiento = Gestion::activo()
            ->where(function ($query) use ($validated) {
                $query->whereBetween('fecha_inicio', [$validated['fecha_inicio'], $validated['fecha_fin']])
                    ->orWhereBetween('fecha_fin', [$validated['fecha_inicio'], $validated['fecha_fin']])
                    ->orWhere(function ($subQuery) use ($validated) {
                        $subQuery->where('fecha_inicio', '<=', $validated['fecha_inicio'])
                            ->where('fecha_fin', '>=', $validated['fecha_fin']);
                    });
            })
            ->exists();

        if ($solapamiento) {
            return back()->withErrors([
                'fecha_inicio' => 'Las fechas se solapan con otra gestión activa.',
            ])->withInput();
        }

        Gestion::create([
            ...$validated,
            'activo' => true,
        ]);

        return redirect()->route('responsable.dashboard')
            ->with('success', 'Gestión académica creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gestion $gestion): Response
    {
        $gestion->load(['cursos.tutor', 'cursos.inscripciones', 'cursos.precios']);

        // Calcular estadísticas detalladas
        $estadisticas = [
            'duracion_dias' => $gestion->duracionEnDias(),
            'total_cursos' => $gestion->cursos->count(),
            'cursos_activos' => $gestion->cursos->where('activo', true)->count(),
            'total_inscripciones' => $gestion->cursos->sum(function ($curso) {
                return $curso->inscripciones->count();
            }),
            'inscripciones_activas' => $gestion->cursos->sum(function ($curso) {
                return $curso->inscripciones->where('estado', '!=', 'RETIRADO')->count();
            }),
            'ingresos_generados' => $gestion->cursos->sum(function ($curso) {
                return $curso->inscripciones->sum(function ($inscripcion) {
                    return $inscripcion->preinscripcion?->pago?->monto ?? 0;
                });
            }),
            'promedio_estudiantes_curso' => $gestion->cursos->count() > 0
                ? round($gestion->cursos->avg('cupos_ocupados'), 1)
                : 0,
        ];

        // Estado actual de la gestión
        $hoy = Carbon::now();
        if ($gestion->fecha_fin < $hoy) {
            $estado = ['nombre' => 'Finalizada', 'color' => 'secondary'];
        } elseif ($gestion->fecha_inicio > $hoy) {
            $estado = ['nombre' => 'Próxima', 'color' => 'info'];
        } else {
            $estado = ['nombre' => 'En Curso', 'color' => 'success'];
        }

        return Inertia::render('Gestiones/Show', [
            'gestion' => $gestion,
            'estadisticas' => $estadisticas,
            'estado' => $estado,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gestion $gestion): Response
    {
        return Inertia::render('Gestiones/Edit', [
            'gestion' => $gestion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Log básico para debugging
            Log::info('Update method called', [
                'id_param' => $id,
                'request_all' => $request->all()
            ]);

            // Buscar la gestión manualmente
            $gestion = Gestion::find($id);

            if (!$gestion) {
                Log::error('Gestión no encontrada', ['id' => $id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Gestión no encontrada'
                ], 404);
            }

            Log::info('Gestión encontrada', ['gestion' => $gestion->toArray()]);

            // Validación mínima
            $validated = $request->validate([
                'nombre' => 'required|string|max:100',
                'descripcion' => 'nullable|string',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date'
            ]);

            // Log antes de update
            Log::info('Before update', ['validated' => $validated]);

            $gestion->update($validated);

            // Log después de update
            Log::info('After update', ['gestion' => $gestion->fresh()->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Gestión actualizada exitosamente.',
                'gestion' => $gestion->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Update error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Desactivar gestión (soft delete)
     */
    public function desactivar(Gestion $gestion)
    {
        // Verificar si tiene cursos activos
        $cursosActivos = $gestion->cursos()->where('activo', true)->count();

        if ($cursosActivos > 0) {
            return back()->with(
                'error',
                "No se puede desactivar la gestión porque tiene {$cursosActivos} curso(s) activo(s)."
            );
        }

        $gestion->update(['activo' => false]);

        return redirect()->route('responsable.dashboard')
            ->with('success', 'Gestión académica desactivada exitosamente.');
    }

    /**
     * Reactivar gestión
     */
    public function reactivar(Gestion $gestion)
    {
        $gestion->update(['activo' => true]);

        return redirect()->route('responsable.dashboard')
            ->with('success', 'Gestión académica reactivada exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(Gestion $gestion)
    {
        if ($gestion->activo) {
            return $this->desactivar($gestion);
        } else {
            return $this->reactivar($gestion);
        }
    }

    /**
     * Obtener gestiones activas para selects
     */
    public function getGestionesActivas()
    {
        $gestiones = Gestion::activo()
            ->select('id', 'nombre', 'fecha_inicio', 'fecha_fin')
            ->orderBy('fecha_inicio', 'desc')
            ->get()
            ->map(function ($gestion) {
                return [
                    'id' => $gestion->id,
                    'nombre' => $gestion->nombre,
                    'periodo' => $gestion->periodo_completo,
                ];
            });

        return response()->json($gestiones);
    }

    /**
     * Obtener gestión actual
     */
    public function getGestionActual()
    {
        $gestionActual = Gestion::actual()->first();

        if (!$gestionActual) {
            return response()->json(['error' => 'No hay gestión activa actualmente'], 404);
        }

        return response()->json([
            'id' => $gestionActual->id,
            'nombre' => $gestionActual->nombre,
            'periodo' => $gestionActual->periodo_completo,
            'dias_restantes' => Carbon::now()->diffInDays($gestionActual->fecha_fin),
        ]);
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $gestionActual = Gestion::actual()->first();

        $stats = [
            'total_gestiones' => Gestion::count(),
            'gestiones_activas' => Gestion::activo()->count(),
            'gestion_actual' => $gestionActual ? [
                'nombre' => $gestionActual->nombre,
                'dias_restantes' => Carbon::now()->diffInDays($gestionActual->fecha_fin),
                'cursos' => $gestionActual->cursos()->activo()->count(),
            ] : null,
            'proximas_gestiones' => Gestion::futura()->activo()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gestion $gestion)
    {
        // Verificar si tiene cursos asociados
        $cursosCount = $gestion->cursos()->count();

        if ($cursosCount > 0) {
            return response()->json([
                'error' => "No se puede eliminar la gestión porque tiene {$cursosCount} curso(s) asociado(s)."
            ], 422);
        }

        $gestion->delete();

        return response()->json([
            'success' => 'Gestión académica eliminada exitosamente.'
        ]);
    }
}
