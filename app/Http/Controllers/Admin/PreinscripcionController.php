<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;
use App\Models\Participante;
use App\Models\Curso;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PreinscripcionController extends Controller
{
    /**
     * Dashboard principal de preinscripciones
     */
    public function index(Request $request): Response
    {
        $query = Preinscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor',
            'curso.precios.tipoParticipante'
        ]);

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('participante', function($pq) use ($search) {
                      $pq->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('carnet', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('curso', function($cq) use ($search) {
                      $cq->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $orderBy = $request->get('order_by', 'fecha_preinscripcion');
        $orderDirection = $request->get('order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Paginación
        $preinscripciones = $query->paginate(15)->withQueryString();

        // Datos adicionales para filtros
        $cursos = Curso::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $estadisticas = [
            'total' => Preinscripcion::count(),
            'pendientes' => Preinscripcion::where('estado', 'PENDIENTE')->count(),
            'aprobadas' => Preinscripcion::where('estado', 'APROBADA')->count(),
            'rechazadas' => Preinscripcion::where('estado', 'RECHAZADA')->count(),
        ];

        return Inertia::render('Admin/Preinscripciones/Index', [
            'preinscripciones' => $preinscripciones,
            'cursos' => $cursos,
            'stats' => $estadisticas,
            'filters' => [
                'estado' => $request->estado,
                'curso_id' => $request->curso_id,
                'search' => $request->search,
                'order_by' => $orderBy,
                'order_direction' => $orderDirection,
            ]
        ]);
    }

    /**
     * Ver detalles de una preinscripción
     */
    public function show($id): Response
    {
        $preinscripcion = Preinscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor',
            'curso.precios.tipoParticipante'
        ])->findOrFail($id);

        // Obtener precio aplicable
        $precioAplicable = $preinscripcion->curso->precios()
            ->where('tipo_participante_id', $preinscripcion->participante->tipo_participante_id)
            ->first();

        // Buscar pago asociado
        $pago = Pago::where('preinscripcion_id', $preinscripcion->id)->first();

        // Buscar inscripción asociada
        $inscripcion = Inscripcion::where('preinscripcion_id', $preinscripcion->id)->first();

        return Inertia::render('Admin/Preinscripciones/Show', [
            'preinscripcion' => $preinscripcion,
            'precioAplicable' => $precioAplicable,
            'pago' => $pago,
            'inscripcion' => $inscripcion,
        ]);
    }

    /**
     * Confirmar preinscripción (crear inscripción oficial)
     */
    public function confirmar(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'monto_pagado' => ['required', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'string', 'in:EFECTIVO,TRANSFERENCIA,DEPOSITO,TARJETA'],
            'referencia_pago' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'monto_pagado.required' => 'El monto pagado es obligatorio.',
            'monto_pagado.numeric' => 'El monto debe ser un número válido.',
            'monto_pagado.min' => 'El monto debe ser mayor a 0.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            'referencia_pago.max' => 'La referencia no puede tener más de 100 caracteres.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                // Buscar preinscripción
                $preinscripcion = Preinscripcion::with(['participante', 'curso'])
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($preinscripcion->estado !== 'PENDIENTE') {
                    throw new \Exception('Solo se pueden confirmar preinscripciones pendientes.');
                }

                // Verificar que el curso sigue teniendo cupos
                $inscripcionesActuales = Inscripcion::where('curso_id', $preinscripcion->curso_id)
                    ->where('estado', 'ACTIVA')
                    ->count();

                if ($inscripcionesActuales >= $preinscripcion->curso->cupo_maximo) {
                    throw new \Exception('El curso ya no tiene cupos disponibles.');
                }

                // Crear inscripción oficial
                $inscripcion = Inscripcion::create([
                    'participante_id' => $preinscripcion->participante_id,
                    'curso_id' => $preinscripcion->curso_id,
                    'fecha_inscripcion' => now(),
                    'estado' => 'ACTIVA',
                    'observaciones' => $validated['observaciones'] ?? 'Inscripción confirmada desde preinscripción',
                    'preinscripcion_id' => $preinscripcion->id,
                ]);

                // Registrar pago
                Pago::create([
                    'inscripcion_id' => $inscripcion->id,
                    'monto' => $validated['monto_pagado'],
                    'fecha_pago' => now(),
                    'metodo_pago' => $validated['metodo_pago'],
                    'referencia' => $validated['referencia_pago'],
                    'estado' => 'CONFIRMADO',
                    'observaciones' => 'Pago registrado por confirmación de preinscripción',
                    'usuario_registro_id' => Auth::id(),
                ]);

                // Actualizar estado de preinscripción
                $preinscripcion->update([
                    'estado' => 'CONFIRMADA',
                    'fecha_confirmacion' => now(),
                    'usuario_confirmacion_id' => Auth::id(),
                ]);
            });

            return redirect()
                ->route('admin.preinscripciones.show', $id)
                ->with('success', 'Preinscripción confirmada exitosamente. El participante ha sido inscrito oficialmente al curso.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al confirmar preinscripción: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Rechazar preinscripción
     */
    public function rechazar(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'motivo_rechazo' => ['required', 'string', 'max:500'],
        ], [
            'motivo_rechazo.required' => 'El motivo de rechazo es obligatorio.',
            'motivo_rechazo.max' => 'El motivo no puede tener más de 500 caracteres.',
        ]);

        try {
            $preinscripcion = Preinscripcion::findOrFail($id);

            if ($preinscripcion->estado !== 'PENDIENTE') {
                throw new \Exception('Solo se pueden rechazar preinscripciones pendientes.');
            }

            $preinscripcion->update([
                'estado' => 'RECHAZADA',
                'fecha_rechazo' => now(),
                'motivo_rechazo' => $validated['motivo_rechazo'],
                'usuario_rechazo_id' => Auth::id(),
            ]);

            return redirect()
                ->route('admin.preinscripciones.show', $id)
                ->with('success', 'Preinscripción rechazada correctamente.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al rechazar preinscripción: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Búsqueda rápida de preinscripciones
     */
    public function buscar(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }

        $preinscripciones = Preinscripcion::with(['participante', 'curso'])
            ->where(function($q) use ($query) {
                $q->where('id', 'like', "%{$query}%")
                  ->orWhereHas('participante', function($pq) use ($query) {
                      $pq->where('nombre', 'like', "%{$query}%")
                        ->orWhere('apellido', 'like', "%{$query}%")
                        ->orWhere('carnet', 'like', "%{$query}%");
                  });
            })
            ->limit(10)
            ->get()
            ->map(function($preinscripcion) {
                return [
                    'id' => $preinscripcion->id,
                    'participante' => $preinscripcion->participante->nombre_completo,
                    'carnet' => $preinscripcion->participante->carnet,
                    'curso' => $preinscripcion->curso->nombre,
                    'estado' => $preinscripcion->estado,
                    'fecha' => $preinscripcion->fecha_preinscripcion->format('d/m/Y'),
                ];
            });

        return response()->json($preinscripciones);
    }

    /**
     * Aprobar una preinscripción (método adicional compatible con las rutas)
     */
    public function aprobar($id): RedirectResponse
    {
        try {
            $preinscripcion = Preinscripcion::findOrFail($id);
            
            if ($preinscripcion->estado !== 'PENDIENTE') {
                return redirect()->back()->withErrors([
                    'error' => 'Solo se pueden aprobar preinscripciones pendientes.'
                ]);
            }

            $preinscripcion->update([
                'estado' => 'APROBADA',
                'observaciones' => ($preinscripcion->observaciones ? $preinscripcion->observaciones . ' | ' : '') . 'Preinscripción aprobada por administrador en ' . now()->format('d/m/Y H:i')
            ]);

            return redirect()->back()->with('success', 'Preinscripción aprobada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al aprobar la preinscripción: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Rechazar preinscripción (método adicional para compatibilidad)
     */
    public function rechazarSimple(Request $request, $id): RedirectResponse
    {
        try {
            $preinscripcion = Preinscripcion::findOrFail($id);
            
            if ($preinscripcion->estado !== 'PENDIENTE') {
                return redirect()->back()->withErrors([
                    'error' => 'Solo se pueden rechazar preinscripciones pendientes.'
                ]);
            }

            $observaciones = $request->input('observaciones', 'Sin observaciones específicas');

            $preinscripcion->update([
                'estado' => 'RECHAZADA',
                'observaciones' => ($preinscripcion->observaciones ? $preinscripcion->observaciones . ' | ' : '') . 'RECHAZADA: ' . $observaciones . ' - Rechazada por administrador en ' . now()->format('d/m/Y H:i')
            ]);

            return redirect()->back()->with('success', 'Preinscripción rechazada.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Error al rechazar la preinscripción: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar preinscripciones a PDF
     */
    public function export(Request $request)
    {
        $query = Preinscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor'
        ]);

        // Aplicar los mismos filtros que en el index
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('curso_id')) {
            $query->where('curso_id', $request->curso_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('participante', function($pq) use ($search) {
                      $pq->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('carnet', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('curso', function($cq) use ($search) {
                      $cq->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        $preinscripciones = $query->orderBy('fecha_preinscripcion', 'desc')->get();

        // Crear vista simple para el PDF si no existe
        $html = view('admin.exports.preinscripciones', [
            'preinscripciones' => $preinscripciones,
            'filtros' => $request->only(['search', 'estado', 'curso_id']),
            'fecha_generacion' => now()->format('d/m/Y H:i')
        ])->render();

        // Generar PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHtml($html)
            ->setPaper('A4', 'landscape');

        return $pdf->download('preinscripciones_' . now()->format('Y-m-d_H-i') . '.pdf');
    }
}
