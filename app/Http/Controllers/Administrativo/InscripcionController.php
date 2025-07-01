<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
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

class InscripcionController extends Controller
{
    /**
     * Constructor - Solo ADMINISTRATIVO puede gestionar inscripciones
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
        $inscripciones = Inscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor',
            'curso.gestion',
            'preinscripcion.pago'
        ])
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
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_inscripcion', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_inscripcion', '<=', $fecha);
            })
            ->when($request->con_notas !== null, function ($query) use ($request) {
                if ($request->boolean('con_notas')) {
                    $query->whereNotNull('nota_final');
                } else {
                    $query->whereNull('nota_final');
                }
            })
            ->orderBy('fecha_inscripcion', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Administrativo/Inscripciones/Index', [
            'inscripciones' => $inscripciones,
            'filters' => $request->only([
                'search',
                'estado',
                'curso_id',
                'tipo_participante_id',
                'fecha_desde',
                'fecha_hasta',
                'con_notas'
            ]),
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'estados' => [
                'INSCRITO' => 'Inscrito',
                'APROBADO' => 'Aprobado',
                'REPROBADO' => 'Reprobado',
                'RETIRADO' => 'Retirado'
            ],
            'estadisticas' => [
                'total' => Inscripcion::count(),
                'inscritos' => Inscripcion::where('estado', 'INSCRITO')->count(),
                'aprobados' => Inscripcion::where('estado', 'APROBADO')->count(),
                'reprobados' => Inscripcion::where('estado', 'REPROBADO')->count(),
                'retirados' => Inscripcion::where('estado', 'RETIRADO')->count(),
                'hoy' => Inscripcion::whereDate('fecha_inscripcion', Carbon::today())->count(),
                'esta_semana' => Inscripcion::whereBetween('fecha_inscripcion', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Obtener preinscripciones listas para inscripción
        $preinscripcionesListas = Preinscripcion::aprobada()
            ->has('pago')
            ->doesntHave('inscripcion')
            ->with(['participante.tipoParticipante', 'curso.tutor', 'pago'])
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->get()
            ->filter(function ($preinscripcion) {
                return $preinscripcion->curso->cupos_disponibles > 0;
            });

        return Inertia::render('Administrativo/Inscripciones/Create', [
            'preinscripcionesListas' => $preinscripcionesListas,
            'cursos' => Curso::activo()->get(['id', 'nombre']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'preinscripcion_id' => ['required', 'exists:PREINSCRIPCION,id'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'preinscripcion_id.required' => 'Debe seleccionar una preinscripción.',
            'preinscripcion_id.exists' => 'La preinscripción seleccionada no es válida.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        DB::transaction(function () use ($validated) {
            $preinscripcion = Preinscripcion::lockForUpdate()->find($validated['preinscripcion_id']);

            // Validaciones de negocio
            if (!$preinscripcion->estaAprobada()) {
                throw new \Exception('La preinscripción debe estar aprobada.');
            }

            if (!$preinscripcion->tienePago()) {
                throw new \Exception('La preinscripción debe tener un pago registrado.');
            }

            if ($preinscripcion->tieneInscripcion()) {
                throw new \Exception('Ya existe una inscripción para esta preinscripción.');
            }

            $curso = Curso::lockForUpdate()->find($preinscripcion->curso_id);

            if ($curso->cupos_disponibles <= 0) {
                throw new \Exception('No hay cupos disponibles en el curso.');
            }

            // Crear la inscripción
            $inscripcion = Inscripcion::create([
                'participante_id' => $preinscripcion->participante_id,
                'curso_id' => $preinscripcion->curso_id,
                'preinscripcion_id' => $preinscripcion->id,
                'fecha_inscripcion' => now(),
                'estado' => Inscripcion::ESTADO_INSCRITO,
                'observaciones' => $validated['observaciones'] ?? 'Inscripción creada desde sistema administrativo.',
            ]);

            // Actualizar cupos ocupados del curso
            $curso->increment('cupos_ocupados');

            $this->inscripcionCreada = $inscripcion;
        });

        return redirect()->route('administrativo.inscripciones.show', $this->inscripcionCreada)
            ->with('success', 'Inscripción creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Inscripcion $inscripcion): Response
    {
        $inscripcion->load([
            'participante.tipoParticipante',
            'curso.tutor',
            'curso.gestion',
            'preinscripcion.pago',
            'asistencias',
            'notasTareas.tarea',
            'certificados'
        ]);

        // Calcular estadísticas del participante en el curso
        $estadisticas = [
            'porcentaje_asistencia' => $inscripcion->porcentajeAsistencia(),
            'promedio_tareas' => round($inscripcion->promedioTareas(), 2),
            'total_tareas' => $inscripcion->notasTareas->count(),
            'tareas_pendientes' => $inscripcion->curso->tareas->count() - $inscripcion->notasTareas->count(),
            'dias_inscrito' => $inscripcion->fecha_inscripcion->diffInDays(Carbon::now()),
            'puede_certificado' => $inscripcion->puedeObtenerCertificado(),
            'tiene_certificado' => $inscripcion->certificados->isNotEmpty(),
        ];

        // Historial académico
        $historialAcademico = [
            'fecha_inscripcion' => $inscripcion->fecha_inscripcion,
            'notas_tareas' => $inscripcion->notasTareas->map(function ($nota) {
                return [
                    'tarea' => $nota->tarea->titulo,
                    'nota' => $nota->nota,
                    'calificacion' => $nota->calificacion_literal,
                    'fecha' => $nota->created_at->format('d/m/Y'),
                ];
            }),
            'resumen_asistencias' => $this->getResumenAsistencias($inscripcion),
        ];

        return Inertia::render('Administrativo/Inscripciones/Show', [
            'inscripcion' => $inscripcion,
            'estadisticas' => $estadisticas,
            'historial_academico' => $historialAcademico,
            'puede_cambiar_estado' => $this->puedeModificarEstado($inscripcion),
            'estados_disponibles' => $this->getEstadosDisponibles($inscripcion),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inscripcion $inscripcion): Response
    {
        return Inertia::render('Administrativo/Inscripciones/Edit', [
            'inscripcion' => $inscripcion->load(['participante', 'curso']),
            'estados' => [
                'INSCRITO' => 'Inscrito',
                'APROBADO' => 'Aprobado',
                'REPROBADO' => 'Reprobado',
                'RETIRADO' => 'Retirado'
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Inscripcion $inscripcion)
    {
        $validated = $request->validate([
            'estado' => ['required', Rule::in(['INSCRITO', 'APROBADO', 'REPROBADO', 'RETIRADO'])],
            'nota_final' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'nota_final.numeric' => 'La nota final debe ser un número.',
            'nota_final.min' => 'La nota final no puede ser menor a 0.',
            'nota_final.max' => 'La nota final no puede ser mayor a 100.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        // Validaciones de negocio para cambio de estado
        if ($validated['estado'] === 'APROBADO' && empty($validated['nota_final'])) {
            return back()->withErrors([
                'nota_final' => 'La nota final es obligatoria para aprobar una inscripción.'
            ])->withInput();
        }

        if ($validated['estado'] === 'APROBADO' && $validated['nota_final'] < 51) {
            return back()->withErrors([
                'nota_final' => 'La nota final debe ser al menos 51 para aprobar.'
            ])->withInput();
        }

        if ($validated['estado'] === 'REPROBADO' && !empty($validated['nota_final']) && $validated['nota_final'] >= 51) {
            return back()->withErrors([
                'nota_final' => 'No se puede reprobar con nota mayor o igual a 51.'
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $inscripcion) {
            $estadoAnterior = $inscripcion->estado;

            $inscripcion->update($validated);

            // Si se retira, liberar cupo
            if ($validated['estado'] === 'RETIRADO' && $estadoAnterior !== 'RETIRADO') {
                $inscripcion->curso->decrement('cupos_ocupados');
            }

            // Si se reactiva, ocupar cupo
            if ($estadoAnterior === 'RETIRADO' && $validated['estado'] !== 'RETIRADO') {
                if ($inscripcion->curso->cupos_disponibles <= 0) {
                    throw new \Exception('No hay cupos disponibles para reactivar la inscripción.');
                }
                $inscripcion->curso->increment('cupos_ocupados');
            }
        });

        return redirect()->route('administrativo.inscripciones.show', $inscripcion)
            ->with('success', 'Inscripción actualizada exitosamente.');
    }

    /**
     * Crear inscripciones masivas desde preinscripciones
     */
    public function crearMasivo(Request $request)
    {
        $validated = $request->validate([
            'preinscripciones' => ['required', 'array', 'min:1'],
            'preinscripciones.*' => ['exists:PREINSCRIPCION,id'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'preinscripciones.required' => 'Debe seleccionar al menos una preinscripción.',
            'preinscripciones.min' => 'Debe seleccionar al menos una preinscripción.',
        ]);

        $creadas = 0;
        $errores = [];

        DB::transaction(function () use ($validated, &$creadas, &$errores) {
            foreach ($validated['preinscripciones'] as $preinscripcionId) {
                try {
                    $preinscripcion = Preinscripcion::lockForUpdate()->find($preinscripcionId);

                    if (!$preinscripcion->estaAprobada()) {
                        $errores[] = "Preinscripción #{$preinscripcionId}: No está aprobada";
                        continue;
                    }

                    if (!$preinscripcion->tienePago()) {
                        $errores[] = "Preinscripción #{$preinscripcionId}: Sin pago registrado";
                        continue;
                    }

                    if ($preinscripcion->tieneInscripcion()) {
                        $errores[] = "Preinscripción #{$preinscripcionId}: Ya tiene inscripción";
                        continue;
                    }

                    $curso = Curso::lockForUpdate()->find($preinscripcion->curso_id);

                    if ($curso->cupos_disponibles <= 0) {
                        $errores[] = "Preinscripción #{$preinscripcionId}: Sin cupos en el curso";
                        continue;
                    }

                    Inscripcion::create([
                        'participante_id' => $preinscripcion->participante_id,
                        'curso_id' => $preinscripcion->curso_id,
                        'preinscripcion_id' => $preinscripcion->id,
                        'fecha_inscripcion' => now(),
                        'estado' => Inscripcion::ESTADO_INSCRITO,
                        'observaciones' => $validated['observaciones'] ?? 'Inscripción masiva.',
                    ]);

                    $curso->increment('cupos_ocupados');
                    $creadas++;
                } catch (\Exception $e) {
                    $errores[] = "Preinscripción #{$preinscripcionId}: Error procesando";
                }
            }
        });

        $mensaje = "Se crearon {$creadas} inscripciones exitosamente.";

        if (!empty($errores)) {
            $mensaje .= " Errores: " . implode(', ', $errores);
            return back()->with('warning', $mensaje);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Retirar inscripción
     */
    public function retirar(Request $request, Inscripcion $inscripcion)
    {
        $validated = $request->validate([
            'observaciones' => ['required', 'string', 'max:500'],
        ], [
            'observaciones.required' => 'Las observaciones son obligatorias para retirar una inscripción.',
            'observaciones.max' => 'Las observaciones no pueden tener más de 500 caracteres.',
        ]);

        if ($inscripcion->estado === 'RETIRADO') {
            return back()->with('error', 'La inscripción ya está retirada.');
        }

        DB::transaction(function () use ($validated, $inscripcion) {
            $inscripcion->update([
                'estado' => 'RETIRADO',
                'observaciones' => $validated['observaciones'],
            ]);

            // Liberar cupo
            $inscripcion->curso->decrement('cupos_ocupados');
        });

        return back()->with('success', 'Inscripción retirada exitosamente. El cupo ha sido liberado.');
    }

    /**
     * Exportar inscripciones
     */
    public function exportar(Request $request)
    {
        $inscripciones = Inscripcion::with([
            'participante.tipoParticipante',
            'curso.tutor',
            'preinscripcion.pago'
        ])
            ->when($request->estado, function ($query, $estado) {
                $query->where('estado', $estado);
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->orderBy('fecha_inscripcion', 'desc')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('d/m/Y H:i'),
                    'participante' => $inscripcion->participante->nombre_completo,
                    'carnet' => $inscripcion->participante->carnet,
                    'email' => $inscripcion->participante->email,
                    'tipo_participante' => $inscripcion->participante->tipoParticipante->descripcion,
                    'curso' => $inscripcion->curso->nombre,
                    'tutor' => $inscripcion->curso->tutor->nombre_completo,
                    'estado' => $inscripcion->estado,
                    'nota_final' => $inscripcion->nota_final ?? 'N/A',
                    'monto_pagado' => $inscripcion->preinscripcion->pago?->monto_formateado ?? 'N/A',
                    'observaciones' => $inscripcion->observaciones,
                ];
            });

        return response()->json($inscripciones)
            ->header('Content-Disposition', 'attachment; filename="inscripciones_cicit.json"');
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'nuevas_hoy' => Inscripcion::whereDate('fecha_inscripcion', Carbon::today())->count(),
            'activas' => Inscripcion::activas()->count(),
            'por_aprobar' => Inscripcion::where('estado', 'INSCRITO')->count(),
            'tasa_aprobacion' => $this->calcularTasaAprobacion(),
            'por_estado' => [
                'inscritos' => Inscripcion::where('estado', 'INSCRITO')->count(),
                'aprobados' => Inscripcion::where('estado', 'APROBADO')->count(),
                'reprobados' => Inscripcion::where('estado', 'REPROBADO')->count(),
                'retirados' => Inscripcion::where('estado', 'RETIRADO')->count(),
            ],
            'cursos_con_mas_inscripciones' => Inscripcion::with('curso')
                ->selectRaw('curso_id, COUNT(*) as total')
                ->groupBy('curso_id')
                ->orderByDesc('total')
                ->take(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'curso' => $item->curso->nombre,
                        'total_inscripciones' => $item->total,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Calcular tasa de aprobación
     */
    private function calcularTasaAprobacion()
    {
        $totalFinalizadas = Inscripcion::whereIn('estado', ['APROBADO', 'REPROBADO'])->count();
        $aprobadas = Inscripcion::where('estado', 'APROBADO')->count();

        return $totalFinalizadas > 0 ? round(($aprobadas / $totalFinalizadas) * 100, 2) : 0;
    }

    /**
     * Verificar si se puede modificar el estado
     */
    private function puedeModificarEstado(Inscripcion $inscripcion)
    {
        return $inscripcion->estado !== 'RETIRADO';
    }

    /**
     * Obtener estados disponibles según el estado actual
     */
    private function getEstadosDisponibles(Inscripcion $inscripcion)
    {
        $todos = [
            'INSCRITO' => 'Inscrito',
            'APROBADO' => 'Aprobado',
            'REPROBADO' => 'Reprobado',
            'RETIRADO' => 'Retirado'
        ];

        if ($inscripcion->estado === 'RETIRADO') {
            return ['RETIRADO' => 'Retirado']; // Solo puede quedar retirado
        }

        return $todos;
    }

    /**
     * Obtener resumen de asistencias
     */
    private function getResumenAsistencias(Inscripcion $inscripcion)
    {
        $asistencias = $inscripcion->asistencias;

        return [
            'total_clases' => $asistencias->count(),
            'presentes' => $asistencias->where('estado', 'PRESENTE')->count(),
            'ausentes' => $asistencias->where('estado', 'AUSENTE')->count(),
            'justificadas' => $asistencias->where('estado', 'JUSTIFICADO')->count(),
            'porcentaje' => $inscripcion->porcentajeAsistencia(),
        ];
    }

    /**
     * Variable para almacenar inscripción creada
     */
    private $inscripcionCreada;
}
