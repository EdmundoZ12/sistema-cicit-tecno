<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Preinscripcion;
use App\Models\Participante;
use App\Models\Curso;
use App\Models\TipoParticipante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoController extends Controller
{
    /**
     * Constructor - Solo ADMINISTRATIVO puede gestionar pagos
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
        $pagos = Pago::with([
            'preinscripcion.participante.tipoParticipante',
            'preinscripcion.curso.tutor'
        ])
            ->when($request->search, function ($query, $search) {
                $query->where('recibo', 'ILIKE', "%{$search}%")
                    ->orWhereHas('preinscripcion.participante', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%")
                            ->orWhere('apellido', 'ILIKE', "%{$search}%")
                            ->orWhere('carnet', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('preinscripcion.curso', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->whereHas('preinscripcion', function ($q) use ($cursoId) {
                    $q->where('curso_id', $cursoId);
                });
            })
            ->when($request->tipo_participante_id, function ($query, $tipoId) {
                $query->whereHas('preinscripcion.participante', function ($q) use ($tipoId) {
                    $q->where('tipo_participante_id', $tipoId);
                });
            })
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_pago', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_pago', '<=', $fecha);
            })
            ->when($request->monto_min, function ($query, $monto) {
                $query->where('monto', '>=', $monto);
            })
            ->when($request->monto_max, function ($query, $monto) {
                $query->where('monto', '<=', $monto);
            })
            ->when($request->con_discrepancia !== null, function ($query) use ($request) {
                if ($request->boolean('con_discrepancia')) {
                    // Pagos que no coinciden con el precio esperado
                    $query->whereRaw('monto != (
                        SELECT precio FROM PRECIO_CURSO pc
                        WHERE pc.curso_id = (SELECT curso_id FROM PREINSCRIPCION p WHERE p.id = PAGO.preinscripcion_id)
                        AND pc.tipo_participante_id = (
                            SELECT tipo_participante_id FROM PARTICIPANTE part
                            WHERE part.id = (SELECT participante_id FROM PREINSCRIPCION p WHERE p.id = PAGO.preinscripcion_id)
                        )
                        AND pc.activo = true
                        LIMIT 1
                    )');
                }
            })
            ->orderBy('fecha_pago', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Enriquecer los pagos con información adicional
        $pagos->getCollection()->transform(function ($pago) {
            $precioEsperado = $pago->preinscripcion->getPrecioAplicable();
            $pago->precio_esperado = $precioEsperado;
            $pago->tiene_discrepancia = abs($pago->monto - $precioEsperado) > 0.01;
            $pago->diferencia = $pago->monto - $precioEsperado;
            return $pago;
        });

        return Inertia::render('Administrativo/Pagos/Index', [
            'pagos' => $pagos,
            'filters' => $request->only([
                'search',
                'curso_id',
                'tipo_participante_id',
                'fecha_desde',
                'fecha_hasta',
                'monto_min',
                'monto_max',
                'con_discrepancia'
            ]),
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'estadisticas' => [
                'total_pagos' => Pago::count(),
                'total_ingresos' => Pago::sum('monto'),
                'ingresos_hoy' => Pago::whereDate('fecha_pago', Carbon::today())->sum('monto'),
                'ingresos_mes' => Pago::whereMonth('fecha_pago', Carbon::now()->month)
                    ->whereYear('fecha_pago', Carbon::now()->year)
                    ->sum('monto'),
                'promedio_pago' => round(Pago::avg('monto'), 2),
                'pagos_hoy' => Pago::whereDate('fecha_pago', Carbon::today())->count(),
                'pagos_pendientes_inscripcion' => Pago::whereHas('preinscripcion', function ($q) {
                    $q->doesntHave('inscripcion');
                })->count(),
                'con_discrepancias' => $this->contarPagosConDiscrepancias(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Obtener preinscripciones aprobadas sin pago
        $preinscripcionesSinPago = Preinscripcion::aprobada()
            ->doesntHave('pago')
            ->with(['participante.tipoParticipante', 'curso.tutor', 'curso.precios.tipoParticipante'])
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('fecha_preinscripcion', 'desc')
            ->get()
            ->map(function ($preinscripcion) {
                $precioAplicable = $preinscripcion->getPrecioAplicable();
                $preinscripcion->precio_aplicable = $precioAplicable;
                $preinscripcion->precio_formateado = 'Bs. ' . number_format($precioAplicable, 2);
                return $preinscripcion;
            });

        return Inertia::render('Administrativo/Pagos/Create', [
            'preinscripcionesSinPago' => $preinscripcionesSinPago,
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'recibo_sugerido' => $this->generarNumeroRecibo(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'preinscripcion_id' => ['required', 'exists:PREINSCRIPCION,id'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'recibo' => ['required', 'string', 'max:50', 'unique:PAGO,recibo'],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'preinscripcion_id.required' => 'Debe seleccionar una preinscripción.',
            'preinscripcion_id.exists' => 'La preinscripción seleccionada no es válida.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'recibo.required' => 'El número de recibo es obligatorio.',
            'recibo.unique' => 'Ya existe un pago con este número de recibo.',
            'recibo.max' => 'El número de recibo no puede tener más de 50 caracteres.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'fecha_pago.date' => 'La fecha de pago debe ser una fecha válida.',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ]);

        // Verificar que la preinscripción no tenga ya un pago
        $preinscripcion = Preinscripcion::find($validated['preinscripcion_id']);

        if ($preinscripcion->tienePago()) {
            return back()->withErrors([
                'preinscripcion_id' => 'Esta preinscripción ya tiene un pago registrado.'
            ])->withInput();
        }

        if (!$preinscripcion->estaAprobada()) {
            return back()->withErrors([
                'preinscripcion_id' => 'Solo se pueden registrar pagos para preinscripciones aprobadas.'
            ])->withInput();
        }

        // Verificar discrepancia en el monto
        $precioEsperado = $preinscripcion->getPrecioAplicable();
        $diferencia = abs($validated['monto'] - $precioEsperado);

        if ($diferencia > 0.01) {
            // Permitir la discrepancia pero mostrar advertencia
            $validated['tiene_discrepancia'] = true;
            $validated['diferencia'] = $validated['monto'] - $precioEsperado;
        }

        $pago = Pago::create([
            'preinscripcion_id' => $validated['preinscripcion_id'],
            'monto' => $validated['monto'],
            'recibo' => strtoupper(trim($validated['recibo'])),
            'fecha_pago' => $validated['fecha_pago'],
        ]);

        $mensaje = 'Pago registrado exitosamente.';

        if (isset($validated['tiene_discrepancia'])) {
            $diferencia = $validated['diferencia'];
            $tipoDiscrepancia = $diferencia > 0 ? 'exceso' : 'falta';
            $mensaje .= " ADVERTENCIA: Hay una discrepancia de {$tipoDiscrepancia} de Bs. " . number_format(abs($diferencia), 2);
        }

        return redirect()->route('administrativo.pagos.show', $pago)
            ->with('success', $mensaje);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago): Response
    {
        $pago->load([
            'preinscripcion.participante.tipoParticipante',
            'preinscripcion.curso.tutor',
            'preinscripcion.curso.gestion',
            'preinscripcion.inscripcion'
        ]);

        // Calcular información adicional
        $precioEsperado = $pago->preinscripcion->getPrecioAplicable();
        $diferencia = $pago->monto - $precioEsperado;
        $tieneDiscrepancia = abs($diferencia) > 0.01;

        $informacionPago = [
            'precio_esperado' => $precioEsperado,
            'precio_esperado_formateado' => 'Bs. ' . number_format($precioEsperado, 2),
            'diferencia' => $diferencia,
            'diferencia_formateada' => 'Bs. ' . number_format($diferencia, 2),
            'tiene_discrepancia' => $tieneDiscrepancia,
            'tipo_discrepancia' => $diferencia > 0 ? 'Exceso' : ($diferencia < 0 ? 'Falta' : null),
            'dias_desde_pago' => $pago->fecha_pago->diffInDays(Carbon::now()),
            'es_pago_reciente' => $pago->esReciente(),
            'tiene_inscripcion' => $pago->preinscripcion->tieneInscripcion(),
        ];

        return Inertia::render('Administrativo/Pagos/Show', [
            'pago' => $pago,
            'informacion_pago' => $informacionPago,
            'puede_editar' => $this->puedeEditar($pago),
            'puede_anular' => $this->puedeAnular($pago),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pago $pago): Response
    {
        $pago->load(['preinscripcion.participante', 'preinscripcion.curso']);

        return Inertia::render('Administrativo/Pagos/Edit', [
            'pago' => $pago,
            'precio_esperado' => $pago->preinscripcion->getPrecioAplicable(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'recibo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('PAGO', 'recibo')->ignore($pago->id)
            ],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'recibo.required' => 'El número de recibo es obligatorio.',
            'recibo.unique' => 'Ya existe otro pago con este número de recibo.',
            'fecha_pago.required' => 'La fecha de pago es obligatoria.',
            'fecha_pago.before_or_equal' => 'La fecha de pago no puede ser futura.',
        ]);

        // Verificar si se puede editar
        if (!$this->puedeEditar($pago)) {
            return back()->with('error', 'No se puede editar este pago porque ya tiene una inscripción asociada.');
        }

        $pago->update([
            'monto' => $validated['monto'],
            'recibo' => strtoupper(trim($validated['recibo'])),
            'fecha_pago' => $validated['fecha_pago'],
        ]);

        return redirect()->route('administrativo.pagos.show', $pago)
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Anular pago (soft delete)
     */
    public function anular(Request $request, Pago $pago)
    {
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ], [
            'motivo.required' => 'El motivo de anulación es obligatorio.',
            'motivo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        if (!$this->puedeAnular($pago)) {
            return back()->with('error', 'No se puede anular este pago porque ya tiene una inscripción asociada.');
        }

        // En lugar de eliminar, marcar como anulado en las observaciones
        // (modificar según tu modelo de negocio)

        return back()->with('error', 'Funcionalidad de anulación pendiente de implementación según políticas del negocio.');
    }

    /**
     * Verificar pagos por lotes
     */
    public function verificarLote(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => ['required', 'date'],
            'fecha_hasta' => ['required', 'date', 'after_or_equal:fecha_desde'],
        ], [
            'fecha_desde.required' => 'La fecha desde es obligatoria.',
            'fecha_hasta.required' => 'La fecha hasta es obligatoria.',
            'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser posterior o igual a la fecha desde.',
        ]);

        $pagos = Pago::with(['preinscripcion.participante.tipoParticipante', 'preinscripcion.curso'])
            ->whereBetween('fecha_pago', [$validated['fecha_desde'], $validated['fecha_hasta']])
            ->get();

        $verificacion = [];
        $totalDiscrepancias = 0;
        $montoTotalDiscrepancias = 0;

        foreach ($pagos as $pago) {
            $precioEsperado = $pago->preinscripcion->getPrecioAplicable();
            $diferencia = $pago->monto - $precioEsperado;
            $tieneDiscrepancia = abs($diferencia) > 0.01;

            if ($tieneDiscrepancia) {
                $totalDiscrepancias++;
                $montoTotalDiscrepancias += abs($diferencia);
            }

            $verificacion[] = [
                'pago_id' => $pago->id,
                'recibo' => $pago->recibo,
                'participante' => $pago->participante->nombre_completo,
                'curso' => $pago->curso->nombre,
                'monto_pagado' => $pago->monto,
                'monto_esperado' => $precioEsperado,
                'diferencia' => $diferencia,
                'tiene_discrepancia' => $tieneDiscrepancia,
                'fecha_pago' => $pago->fecha_pago->format('d/m/Y'),
            ];
        }

        return response()->json([
            'total_pagos' => $pagos->count(),
            'total_discrepancias' => $totalDiscrepancias,
            'monto_total_discrepancias' => $montoTotalDiscrepancias,
            'pagos' => $verificacion,
        ]);
    }

    /**
     * Exportar pagos
     */
    public function exportar(Request $request)
    {
        $pagos = Pago::with([
            'preinscripcion.participante.tipoParticipante',
            'preinscripcion.curso.tutor'
        ])
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_pago', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_pago', '<=', $fecha);
            })
            ->orderBy('fecha_pago', 'desc')
            ->get()
            ->map(function ($pago) {
                $precioEsperado = $pago->preinscripcion->getPrecioAplicable();
                $diferencia = $pago->monto - $precioEsperado;

                return [
                    'fecha_pago' => $pago->fecha_pago->format('d/m/Y'),
                    'recibo' => $pago->recibo,
                    'participante' => $pago->participante->nombre_completo,
                    'carnet' => $pago->participante->carnet,
                    'tipo_participante' => $pago->participante->tipoParticipante->descripcion,
                    'curso' => $pago->curso->nombre,
                    'tutor' => $pago->curso->tutor->nombre_completo,
                    'monto_pagado' => $pago->monto,
                    'monto_esperado' => $precioEsperado,
                    'diferencia' => $diferencia,
                    'tiene_discrepancia' => abs($diferencia) > 0.01 ? 'Sí' : 'No',
                ];
            });

        return response()->json($pagos)
            ->header('Content-Disposition', 'attachment; filename="pagos_cicit.json"');
    }

    /**
     * Estadísticas financieras
     */
    public function estadisticasFinancieras(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth());
        $fechaFin = $request->input('fecha_fin', Carbon::now()->endOfMonth());

        $stats = [
            'ingresos_periodo' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->sum('monto'),
            'total_pagos_periodo' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->count(),
            'promedio_pago_periodo' => Pago::whereBetween('fecha_pago', [$fechaInicio, $fechaFin])->avg('monto'),
            'ingresos_por_tipo' => $this->getIngresosPorTipoParticipante($fechaInicio, $fechaFin),
            'ingresos_por_curso' => $this->getIngresosPorCurso($fechaInicio, $fechaFin),
            'evolucion_diaria' => $this->getEvolucionDiaria($fechaInicio, $fechaFin),
            'metodo_pago_mas_usado' => 'Transferencia bancaria', // Placeholder
        ];

        return response()->json($stats);
    }

    /**
     * Obtener pagos pendientes de inscripción
     */
    public function getPagosPendientesInscripcion()
    {
        $pagosPendientes = Pago::whereHas('preinscripcion', function ($q) {
            $q->doesntHave('inscripcion');
        })
            ->with(['preinscripcion.participante', 'preinscripcion.curso'])
            ->orderBy('fecha_pago', 'asc')
            ->take(20)
            ->get()
            ->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'recibo' => $pago->recibo,
                    'participante' => $pago->participante->nombre_completo,
                    'curso' => $pago->curso->nombre,
                    'monto' => $pago->monto_formateado,
                    'fecha_pago' => $pago->fecha_pago->format('d/m/Y'),
                    'dias_desde_pago' => $pago->fecha_pago->diffInDays(Carbon::now()),
                ];
            });

        return response()->json($pagosPendientes);
    }

    /**
     * Generar número de recibo sugerido
     */
    private function generarNumeroRecibo()
    {
        $año = Carbon::now()->year;
        $mes = Carbon::now()->format('m');
        $ultimoPago = Pago::whereYear('fecha_pago', $año)
            ->whereMonth('fecha_pago', $mes)
            ->count();

        $numero = str_pad($ultimoPago + 1, 4, '0', STR_PAD_LEFT);

        return "REC-{$año}{$mes}-{$numero}";
    }

    /**
     * Verificar si se puede editar un pago
     */
    private function puedeEditar(Pago $pago)
    {
        return !$pago->preinscripcion->tieneInscripcion();
    }

    /**
     * Verificar si se puede anular un pago
     */
    private function puedeAnular(Pago $pago)
    {
        return !$pago->preinscripcion->tieneInscripcion() && $pago->esReciente();
    }

    /**
     * Contar pagos con discrepancias
     */
    private function contarPagosConDiscrepancias()
    {
        // Esta consulta puede ser costosa, considera cachearla
        return DB::table('PAGO')
            ->join('PREINSCRIPCION', 'PAGO.preinscripcion_id', '=', 'PREINSCRIPCION.id')
            ->join('PARTICIPANTE', 'PREINSCRIPCION.participante_id', '=', 'PARTICIPANTE.id')
            ->join('PRECIO_CURSO', function ($join) {
                $join->on('PREINSCRIPCION.curso_id', '=', 'PRECIO_CURSO.curso_id')
                    ->on('PARTICIPANTE.tipo_participante_id', '=', 'PRECIO_CURSO.tipo_participante_id')
                    ->where('PRECIO_CURSO.activo', true);
            })
            ->whereRaw('ABS(PAGO.monto - PRECIO_CURSO.precio) > 0.01')
            ->count();
    }

    /**
     * Obtener ingresos por tipo de participante
     */
    private function getIngresosPorTipoParticipante($fechaInicio, $fechaFin)
    {
        return DB::table('PAGO')
            ->join('PREINSCRIPCION', 'PAGO.preinscripcion_id', '=', 'PREINSCRIPCION.id')
            ->join('PARTICIPANTE', 'PREINSCRIPCION.participante_id', '=', 'PARTICIPANTE.id')
            ->join('TIPO_PARTICIPANTE', 'PARTICIPANTE.tipo_participante_id', '=', 'TIPO_PARTICIPANTE.id')
            ->whereBetween('PAGO.fecha_pago', [$fechaInicio, $fechaFin])
            ->groupBy('TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion')
            ->select('TIPO_PARTICIPANTE.codigo', 'TIPO_PARTICIPANTE.descripcion', DB::raw('SUM(PAGO.monto) as total'))
            ->get();
    }

    /**
     * Obtener ingresos por curso
     */
    private function getIngresosPorCurso($fechaInicio, $fechaFin)
    {
        return DB::table('PAGO')
            ->join('PREINSCRIPCION', 'PAGO.preinscripcion_id', '=', 'PREINSCRIPCION.id')
            ->join('CURSO', 'PREINSCRIPCION.curso_id', '=', 'CURSO.id')
            ->whereBetween('PAGO.fecha_pago', [$fechaInicio, $fechaFin])
            ->groupBy('CURSO.id', 'CURSO.nombre')
            ->select('CURSO.nombre', DB::raw('SUM(PAGO.monto) as total'), DB::raw('COUNT(*) as cantidad_pagos'))
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * Obtener evolución diaria de ingresos
     */
    private function getEvolucionDiaria($fechaInicio, $fechaFin)
    {
        return DB::table('PAGO')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->groupBy(DB::raw('DATE(fecha_pago)'))
            ->select(DB::raw('DATE(fecha_pago) as fecha'), DB::raw('SUM(monto) as total'))
            ->orderBy('fecha')
            ->get();
    }
}
