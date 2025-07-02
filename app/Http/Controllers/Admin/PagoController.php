<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;
use App\Models\Participante;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class PagoController extends Controller
{
    /**
     * Mostrar listado de pagos
     */
    public function index(Request $request): Response
    {
        $query = Pago::with([
            'preinscripcion.participante',
            'preinscripcion.curso'
        ]);

        // Aplicar filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recibo', 'like', "%{$search}%")
                  ->orWhereHas('preinscripcion', function ($pq) use ($search) {
                      $pq->where('id', 'like', "%{$search}%")
                        ->orWhereHas('participante', function ($ppq) use ($search) {
                            $ppq->where('nombre', 'ilike', "%{$search}%")
                              ->orWhere('apellido', 'ilike', "%{$search}%")
                              ->orWhere('carnet', 'like', "%{$search}%");
                        });
                  });
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_hasta);
        }

        // Obtener resultados paginados
        $pagos = $query->orderBy('fecha_pago', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Estadísticas
        $stats = [
            'total_pagos' => Pago::count(),
            'pagos_hoy' => Pago::whereDate('fecha_pago', today())->count(),
            'ingresos_mes' => Pago::whereMonth('fecha_pago', now()->month)
                ->whereYear('fecha_pago', now()->year)
                ->sum('monto'),
            'ingresos_total' => Pago::sum('monto'),
        ];

        return Inertia::render('Admin/Pagos/Index', [
            'pagos' => $pagos,
            'stats' => $stats,
            'filters' => $request->only(['search', 'fecha_desde', 'fecha_hasta'])
        ]);
    }

    /**
     * Mostrar formulario para registrar pago
     */
    public function create(Request $request): Response
    {
        $preinscripcionId = $request->get('preinscripcion');
        $preinscripcion = null;

        if ($preinscripcionId) {
            $preinscripcion = Preinscripcion::with([
                'participante.tipoParticipante',
                'curso.precios.tipoParticipante',
                'curso.tutor'
            ])->find($preinscripcionId);
        }

        return Inertia::render('Admin/Pagos/Create', [
            'preinscripcion' => $preinscripcion,
        ]);
    }

    /**
     * Buscar preinscripción por ID
     */
    public function buscarPreinscripcion(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:PREINSCRIPCION,id'
        ]);

        $preinscripcion = Preinscripcion::with([
            'participante.tipoParticipante',
            'curso.precios.tipoParticipante',
            'curso.tutor'
        ])->find($request->id);

        if (!$preinscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Preinscripción no encontrada'
            ], 404);
        }

        // Verificar estado
        if ($preinscripcion->estado !== 'APROBADA') {
            return response()->json([
                'success' => false,
                'message' => 'La preinscripción debe estar APROBADA para procesar el pago. Estado actual: ' . $preinscripcion->estado
            ], 400);
        }

        // Verificar si ya tiene pago
        $pagoExistente = Pago::where('preinscripcion_id', $preinscripcion->id)->first();
        if ($pagoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Esta preinscripción ya tiene un pago registrado',
                'pago_existente' => $pagoExistente
            ], 400);
        }

        // Verificar si ya tiene inscripción
        $inscripcionExistente = Inscripcion::where('preinscripcion_id', $preinscripcion->id)->first();
        if ($inscripcionExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Esta preinscripción ya tiene una inscripción oficial',
                'inscripcion_existente' => $inscripcionExistente
            ], 400);
        }

        // Obtener precio aplicable
        $precioAplicable = $preinscripcion->curso->precios()
            ->where('tipo_participante_id', $preinscripcion->participante->tipo_participante_id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'preinscripcion' => $preinscripcion,
                'precio_aplicable' => $precioAplicable,
                'participante' => $preinscripcion->participante,
                'curso' => $preinscripcion->curso,
            ]
        ]);
    }

    /**
     * Procesar pago y crear inscripción oficial
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preinscripcion_id' => 'required|integer|exists:PREINSCRIPCION,id',
            'monto' => 'required|numeric|min:0',
            'recibo' => 'required|string|max:50|unique:PAGO,recibo',
            'observaciones' => 'nullable|string|max:500',
        ], [
            'preinscripcion_id.required' => 'La preinscripción es obligatoria.',
            'preinscripcion_id.exists' => 'La preinscripción seleccionada no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor o igual a 0.',
            'recibo.required' => 'El número de recibo es obligatorio.',
            'recibo.max' => 'El número de recibo no puede tener más de 50 caracteres.',
            'recibo.unique' => 'Ya existe un pago con este número de recibo.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Buscar preinscripción
                $preinscripcion = Preinscripcion::with(['participante', 'curso'])
                    ->lockForUpdate()
                    ->findOrFail($validated['preinscripcion_id']);

                // Verificar estado
                if ($preinscripcion->estado !== 'APROBADA') {
                    throw new \Exception('Solo se pueden procesar pagos de preinscripciones aprobadas. Estado actual: ' . $preinscripcion->estado);
                }

                // Verificar que no tenga pago previo
                $pagoExistente = Pago::where('preinscripcion_id', $preinscripcion->id)->first();
                if ($pagoExistente) {
                    throw new \Exception('Esta preinscripción ya tiene un pago registrado.');
                }

                // Verificar que no tenga inscripción previa
                $inscripcionExistente = Inscripcion::where('preinscripcion_id', $preinscripcion->id)->first();
                if ($inscripcionExistente) {
                    throw new \Exception('Esta preinscripción ya tiene una inscripción oficial.');
                }

                // Verificar cupos disponibles
                if (!$preinscripcion->curso->tieneCuposDisponibles()) {
                    throw new \Exception('El curso ya no tiene cupos disponibles.');
                }

                // 1. Crear el pago
                $pago = Pago::create([
                    'preinscripcion_id' => $preinscripcion->id,
                    'monto' => $validated['monto'],
                    'recibo' => $validated['recibo'],
                    'fecha_pago' => now(),
                ]);

                // 2. Crear la inscripción oficial
                $inscripcion = Inscripcion::create([
                    'participante_id' => $preinscripcion->participante_id,
                    'curso_id' => $preinscripcion->curso_id,
                    'preinscripcion_id' => $preinscripcion->id,
                    'fecha_inscripcion' => now(),
                    'estado' => 'INSCRITO',
                    'observaciones' => $validated['observaciones'] ?? 'Inscripción creada automáticamente tras procesamiento de pago.',
                ]);

                // 3. Actualizar estado de preinscripción (opcional)
                $preinscripcion->update([
                    'observaciones' => ($preinscripcion->observaciones ? $preinscripcion->observaciones . ' | ' : '') . 
                                     'PAGO PROCESADO: Recibo ' . $validated['recibo'] . ' - Inscripción oficial creada en ' . now()->format('d/m/Y H:i')
                ]);
            });

            return redirect()
                ->route('admin.pagos.index')
                ->with('success', 'Pago procesado exitosamente. El participante ha sido inscrito oficialmente al curso.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al procesar el pago: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar detalles de un pago
     */
    public function show($id): Response
    {
        $pago = Pago::with([
            'preinscripcion.participante.tipoParticipante',
            'preinscripcion.curso.tutor'
        ])->findOrFail($id);

        // Buscar la inscripción asociada
        $inscripcion = Inscripcion::where('preinscripcion_id', $pago->preinscripcion_id)->first();

        return Inertia::render('Admin/Pagos/Show', [
            'pago' => $pago,
            'inscripcion' => $inscripcion,
        ]);
    }
}
