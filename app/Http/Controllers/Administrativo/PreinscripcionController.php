<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Preinscripcion;
use App\Models\Participante;
use App\Models\Curso;
use App\Models\TipoParticipante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PreinscripcionController extends Controller
{
    /**
     * Constructor - Solo ADMINISTRATIVO puede gestionar preinscripciones
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMINISTRATIVO']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $preinscripciones = Preinscripcion::with(['participante.tipoParticipante', 'curso.tutor', 'pago'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('participante', function ($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido', 'ILIKE', "%{$search}%")
                        ->orWhere('carnet', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                })
                    ->orWhereHas('curso', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->when($request->tipo_participante_id, function ($query, $tipoId) {
                $query->whereHas('participante', function ($q) use ($tipoId) {
                    $q->where('tipo_participante_id', $tipoId);
                });
            })
            ->when($request->con_pago !== null, function ($query) use ($request) {
                if ($request->boolean('con_pago')) {
                    $query->has('pago');
                } else {
                    $query->doesntHave('pago');
                }
            })
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_preinscripcion', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_preinscripcion', '<=', $fecha);
            })
            ->orderBy('fecha_preinscripcion', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Administrativo/Preinscripciones/Index', [
            'preinscripciones' => $preinscripciones,
            'filters' => $request->only([
                'search',
                'estado',
                'curso_id',
                'tipo_participante_id',
                'con_pago',
                'fecha_desde',
                'fecha_hasta'
            ]),
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'estados' => [
                'PENDIENTE' => 'Pendiente de revisión',
                'APROBADA' => 'Aprobada',
                'RECHAZADA' => 'Rechazada'
            ],
            'estadisticas' => [
                'total' => Preinscripcion::count(),
                'pendientes' => Preinscripcion::pendiente()->count(),
                'aprobadas' => Preinscripcion::aprobada()->count(),
                'rechazadas' => Preinscripcion::rechazada()->count(),
                'con_pago' => Preinscripcion::has('pago')->count(),
                'hoy' => Preinscripcion::whereDate('fecha_preinscripcion', Carbon::today())->count(),
                'esta_semana' => Preinscripcion::whereBetween('fecha_preinscripcion', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Preinscripcion $preinscripcion): Response
    {
        $preinscripcion->load([
            'participante.tipoParticipante',
            'curso.tutor',
            'curso.gestion',
            'curso.precios.tipoParticipante',
            'pago',
            'inscripcion'
        ]);

        // Calcular precio aplicable
        $precioAplicable = $preinscripcion->getPrecioAplicable();

        // Verificar disponibilidad en el curso
        $disponibilidadCurso = [
            'cupos_disponibles' => $preinscripcion->curso->cupos_disponibles,
            'puede_inscribirse' => $preinscripcion->curso->cupos_disponibles > 0 &&
                $preinscripcion->curso->fecha_inicio > Carbon::now(),
            'curso_iniciado' => $preinscripcion->curso->fecha_inicio <= Carbon::now(),
        ];

        // Historial de cambios de estado (simulado)
        $historialEstados = [
            [
                'estado' => 'PENDIENTE',
                'fecha' => $preinscripcion->fecha_preinscripcion,
                'observacion' => 'Preinscripción realizada desde formulario público',
            ]
        ];

        if ($preinscripcion->estado !== 'PENDIENTE') {
            $historialEstados[] = [
                'estado' => $preinscripcion->estado,
                'fecha' => $preinscripcion->updated_at,
                'observacion' => $preinscripcion->observaciones,
            ];
        }

        return Inertia::render('Administrativo/Preinscripciones/Show', [
            'preinscripcion' => $preinscripcion,
            'precio_aplicable' => $precioAplicable,
            'precio_formateado' => 'Bs. ' . number_format($precioAplicable, 2),
            'disponibilidad_curso' => $disponibilidadCurso,
            'historial_estados' => $historialEstados,
            'puede_aprobar' => $this->puedeAprobar($preinscripcion),
            'puede_rechazar' => $this->puedeRechazar($preinscripcion),
            'puede_crear_inscripcion' => $this->puedeCrearInscripcion($preinscripcion),
        ]);
    }

    /**
     * Aprobar preinscripción
     */
    public function aprobar(Request $request, Preinscripcion $preinscripcion)
    {
        $validated = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        // Verificar que esté pendiente
        if (!$preinscripcion->estaPendiente()) {
            return back()->with('error', 'Solo se pueden aprobar preinscripciones pendientes.');
        }

        // Verificar disponibilidad de cupos
        if ($preinscripcion->curso->cupos_disponibles <= 0) {
            return back()->with('error', 'No hay cupos disponibles en este curso.');
        }

        // Verificar que el curso no haya iniciado
        if ($preinscripcion->curso->fecha_inicio <= Carbon::now()) {
            return back()->with('error', 'No se puede aprobar porque el curso ya ha iniciado.');
        }

        $preinscripcion->update([
            'estado' => Preinscripcion::ESTADO_APROBADA,
            'observaciones' => $validated['observaciones'] ?? 'Preinscripción aprobada para proceder con el pago.',
        ]);

        return back()->with('success', 'Preinscripción aprobada exitosamente. El participante puede proceder con el pago.');
    }

    /**
     * Rechazar preinscripción
     */
    public function rechazar(Request $request, Preinscripcion $preinscripcion)
    {
        $validated = $request->validate([
            'observaciones' => ['required', 'string', 'max:500'],
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias al rechazar una preinscripción.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        // Verificar que esté pendiente
        if (!$preinscripcion->estaPendiente()) {
            return back()->with('error', 'Solo se pueden rechazar preinscripciones pendientes.');
        }

        $preinscripcion->update([
            'estado' => Preinscripcion::ESTADO_RECHAZADA,
            'observaciones' => $validated['observaciones'],
        ]);

        return back()->with('success', 'Preinscripción rechazada exitosamente.');
    }

    /**
     * Revertir estado a pendiente
     */
    public function revertirAPendiente(Request $request, Preinscripcion $preinscripcion)
    {
        $validated = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        // Verificar que no esté pendiente
        if ($preinscripcion->estaPendiente()) {
            return back()->with('error', 'La preinscripción ya está pendiente.');
        }

        // Verificar que no tenga inscripción creada
        if ($preinscripcion->tieneInscripcion()) {
            return back()->with('error', 'No se puede revertir porque ya tiene una inscripción asociada.');
        }

        $preinscripcion->update([
            'estado' => Preinscripcion::ESTADO_PENDIENTE,
            'observaciones' => $validated['observaciones'] ?? 'Estado revertido a pendiente para nueva revisión.',
        ]);

        return back()->with('success', 'Preinscripción revertida a estado pendiente.');
    }

    /**
     * Actualizar observaciones
     */
    public function actualizarObservaciones(Request $request, Preinscripcion $preinscripcion)
    {
        $validated = $request->validate([
            'observaciones' => ['required', 'string', 'max:500'],
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        $preinscripcion->update([
            'observaciones' => $validated['observaciones'],
        ]);

        return back()->with('success', 'Observaciones actualizadas exitosamente.');
    }

    /**
     * Obtener preinscripciones pendientes para dashboard
     */
    public function getPendientes()
    {
        $pendientes = Preinscripcion::pendiente()
            ->with(['participante', 'curso'])
            ->orderBy('fecha_preinscripcion', 'desc')
            ->take(10)
            ->get()
            ->map(function ($preinscripcion) {
                return [
                    'id' => $preinscripcion->id,
                    'participante' => $preinscripcion->participante->nombre_completo,
                    'curso' => $preinscripcion->curso->nombre,
                    'fecha' => $preinscripcion->fecha_preinscripcion->format('d/m/Y H:i'),
                    'dias_pendiente' => $preinscripcion->fecha_preinscripcion->diffInDays(Carbon::now()),
                ];
            });

        return response()->json($pendientes);
    }

    /**
     * Procesar múltiples preinscripciones
     */
    public function procesarMasivo(Request $request)
    {
        $validated = $request->validate([
            'preinscripciones' => ['required', 'array', 'min:1'],
            'preinscripciones.*' => ['exists:PREINSCRIPCION,id'],
            'accion' => ['required', 'in:aprobar,rechazar'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'preinscripciones.required' => 'Debe seleccionar al menos una preinscripción.',
            'accion.required' => 'Debe seleccionar una acción.',
            'accion.in' => 'Acción no válida.',
        ]);

        $procesadas = 0;
        $errores = [];

        foreach ($validated['preinscripciones'] as $id) {
            try {
                $preinscripcion = Preinscripcion::find($id);

                if (!$preinscripcion->estaPendiente()) {
                    $errores[] = "Preinscripción #{$id}: No está pendiente";
                    continue;
                }

                if ($validated['accion'] === 'aprobar') {
                    if ($preinscripcion->curso->cupos_disponibles <= 0) {
                        $errores[] = "Preinscripción #{$id}: Sin cupos disponibles";
                        continue;
                    }

                    $preinscripcion->update([
                        'estado' => Preinscripcion::ESTADO_APROBADA,
                        'observaciones' => $validated['observaciones'] ?? 'Aprobada en procesamiento masivo.',
                    ]);
                } else {
                    if (empty($validated['observaciones'])) {
                        $errores[] = "Preinscripción #{$id}: Observaciones requeridas para rechazar";
                        continue;
                    }

                    $preinscripcion->update([
                        'estado' => Preinscripcion::ESTADO_RECHAZADA,
                        'observaciones' => $validated['observaciones'],
                    ]);
                }

                $procesadas++;
            } catch (\Exception $e) {
                $errores[] = "Preinscripción #{$id}: Error procesando";
            }
        }

        $mensaje = "Se procesaron {$procesadas} preinscripciones exitosamente.";

        if (!empty($errores)) {
            $mensaje .= " Errores: " . implode(', ', $errores);
            return back()->with('warning', $mensaje);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Exportar preinscripciones
     */
    public function exportar(Request $request)
    {
        $preinscripciones = Preinscripcion::with(['participante.tipoParticipante', 'curso', 'pago'])
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('fecha_preinscripcion', 'desc')
            ->get()
            ->map(function ($preinscripcion) {
                return [
                    'fecha_preinscripcion' => $preinscripcion->fecha_preinscripcion->format('d/m/Y H:i'),
                    'participante' => $preinscripcion->participante->nombre_completo,
                    'carnet' => $preinscripcion->participante->carnet,
                    'email' => $preinscripcion->participante->email,
                    'tipo_participante' => $preinscripcion->participante->tipoParticipante->descripcion,
                    'curso' => $preinscripcion->curso->nombre,
                    'estado' => $preinscripcion->estado,
                    'tiene_pago' => $preinscripcion->tienePago() ? 'Sí' : 'No',
                    'monto_pago' => $preinscripcion->pago?->monto_formateado ?? 'N/A',
                    'observaciones' => $preinscripcion->observaciones,
                ];
            });

        return response()->json($preinscripciones)
            ->header('Content-Disposition', 'attachment; filename="preinscripciones_cicit.json"');
    }

    /**
     * Estadísticas para dashboard administrativo
     */
    public function estadisticas()
    {
        $stats = [
            'pendientes_revision' => Preinscripcion::pendiente()->count(),
            'aprobadas_sin_pago' => Preinscripcion::aprobada()->doesntHave('pago')->count(),
            'nuevas_hoy' => Preinscripcion::whereDate('fecha_preinscripcion', Carbon::today())->count(),
            'promedio_diario' => Preinscripcion::whereMonth('fecha_preinscripcion', Carbon::now()->month)
                ->count() / Carbon::now()->day,
            'por_estado' => [
                'pendientes' => Preinscripcion::pendiente()->count(),
                'aprobadas' => Preinscripcion::aprobada()->count(),
                'rechazadas' => Preinscripcion::rechazada()->count(),
            ],
            'cursos_mas_solicitados' => Preinscripcion::with('curso')
                ->selectRaw('curso_id, COUNT(*) as total')
                ->groupBy('curso_id')
                ->orderByDesc('total')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'curso' => $item->curso->nombre,
                        'total_preinscripciones' => $item->total,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Verificar si se puede aprobar la preinscripción
     */
    private function puedeAprobar(Preinscripcion $preinscripcion)
    {
        return $preinscripcion->estaPendiente() &&
            $preinscripcion->curso->cupos_disponibles > 0 &&
            $preinscripcion->curso->fecha_inicio > Carbon::now();
    }

    /**
     * Verificar si se puede rechazar la preinscripción
     */
    private function puedeRechazar(Preinscripcion $preinscripcion)
    {
        return $preinscripcion->estaPendiente();
    }

    /**
     * Verificar si se puede crear inscripción
     */
    private function puedeCrearInscripcion(Preinscripcion $preinscripcion)
    {
        return $preinscripcion->estaAprobada() &&
            $preinscripcion->tienePago() &&
            !$preinscripcion->tieneInscripcion() &&
            $preinscripcion->curso->cupos_disponibles > 0;
    }
}
