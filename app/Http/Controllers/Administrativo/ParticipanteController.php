<?php

namespace App\Http\Controllers\Administrativo;

use App\Http\Controllers\Controller;
use App\Models\Participante;
use App\Models\TipoParticipante;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ParticipanteController extends Controller
{
    /**
     * Constructor - Solo ADMINISTRATIVO puede gestionar participantes
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
        $participantes = Participante::with(['tipoParticipante'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('carnet', 'ILIKE', "%{$search}%")
                        ->orWhere('nombre', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhere('registro', 'ILIKE', "%{$search}%");
                });
            })
            ->when($request->tipo_participante_id, function ($query, $tipoId) {
                $query->where('tipo_participante_id', $tipoId);
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->when($request->con_preinscripciones, function ($query) {
                $query->has('preinscripciones');
            })
            ->when($request->con_inscripciones, function ($query) {
                $query->has('inscripciones');
            })
            ->when($request->universidad, function ($query, $universidad) {
                $query->where('universidad', 'ILIKE', "%{$universidad}%");
            })
            ->withCount([
                'preinscripciones',
                'inscripciones',
                'preinscripciones as preinscripciones_pendientes_count' => function ($query) {
                    $query->where('estado', 'PENDIENTE');
                },
                'inscripciones as inscripciones_activas_count' => function ($query) {
                    $query->where('estado', '!=', 'RETIRADO');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Administrativo/Participantes/Index', [
            'participantes' => $participantes,
            'filters' => $request->only([
                'search',
                'tipo_participante_id',
                'activo',
                'con_preinscripciones',
                'con_inscripciones',
                'universidad'
            ]),
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'universidades' => $this->getUniversidadesList(),
            'estadisticas' => [
                'total' => Participante::count(),
                'activos' => Participante::activo()->count(),
                'con_preinscripciones' => Participante::has('preinscripciones')->count(),
                'con_inscripciones' => Participante::has('inscripciones')->count(),
                'nuevos_este_mes' => Participante::whereMonth('created_at', now()->month)->count(),
                'por_tipo' => TipoParticipante::withCount('participantes')->get(['codigo', 'participantes_count']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Administrativo/Participantes/Create', [
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'universidadesSugeridas' => $this->getUniversidadesSugeridas(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'carnet' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:PARTICIPANTE,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'universidad' => ['nullable', 'string', 'max:255'],
            'tipo_participante_id' => ['required', 'exists:TIPO_PARTICIPANTE,id'],
        ], [
            'carnet.required' => 'El número de carnet es obligatorio.',
            'carnet.max' => 'El carnet no puede tener más de 20 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe proporcionar un correo electrónico válido.',
            'email.unique' => 'Ya existe un participante con este correo electrónico.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'universidad.max' => 'El nombre de la universidad no puede tener más de 255 caracteres.',
            'tipo_participante_id.required' => 'Debe seleccionar el tipo de participante.',
            'tipo_participante_id.exists' => 'El tipo de participante seleccionado no es válido.',
        ]);

        // Verificar duplicado por carnet y tipo
        $duplicado = Participante::where('carnet', $validated['carnet'])
            ->where('tipo_participante_id', $validated['tipo_participante_id'])
            ->first();

        if ($duplicado) {
            return back()->withErrors([
                'carnet' => 'Ya existe un participante con este carnet para el tipo seleccionado.'
            ])->withInput();
        }

        $participante = Participante::create([
            ...$validated,
            'activo' => true,
            'registro' => $this->generarRegistroParticipante($validated),
        ]);

        return redirect()->route('administrativo.participantes.index')
            ->with('success', 'Participante creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Participante $participante): Response
    {
        $participante->load([
            'tipoParticipante',
            'preinscripciones.curso.tutor',
            'preinscripciones.pago',
            'inscripciones.curso.tutor',
            'inscripciones.certificados'
        ]);

        // Estadísticas del participante
        $estadisticas = [
            'total_preinscripciones' => $participante->preinscripciones->count(),
            'preinscripciones_pendientes' => $participante->preinscripciones->where('estado', 'PENDIENTE')->count(),
            'preinscripciones_aprobadas' => $participante->preinscripciones->where('estado', 'APROBADA')->count(),
            'total_inscripciones' => $participante->inscripciones->count(),
            'inscripciones_activas' => $participante->inscripciones->where('estado', '!=', 'RETIRADO')->count(),
            'cursos_aprobados' => $participante->inscripciones->where('estado', 'APROBADO')->count(),
            'certificados_obtenidos' => $participante->inscripciones->sum(function ($inscripcion) {
                return $inscripcion->certificados->count();
            }),
            'pagos_realizados' => $participante->preinscripciones->whereNotNull('pago')->count(),
            'monto_total_pagado' => $participante->preinscripciones->sum(function ($preinscripcion) {
                return $preinscripcion->pago?->monto ?? 0;
            }),
        ];

        // Historial académico
        $historialAcademico = $participante->inscripciones->map(function ($inscripcion) {
            return [
                'curso' => $inscripcion->curso->nombre,
                'tutor' => $inscripcion->curso->tutor->nombre_completo,
                'fecha_inscripcion' => $inscripcion->fecha_inscripcion->format('d/m/Y'),
                'estado' => $inscripcion->estado,
                'nota_final' => $inscripcion->nota_final,
                'tiene_certificado' => $inscripcion->certificados->isNotEmpty(),
            ];
        });

        return Inertia::render('Administrativo/Participantes/Show', [
            'participante' => $participante,
            'estadisticas' => $estadisticas,
            'historialAcademico' => $historialAcademico,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Participante $participante): Response
    {
        return Inertia::render('Administrativo/Participantes/Edit', [
            'participante' => $participante,
            'tiposParticipante' => TipoParticipante::activo()->get(['id', 'codigo', 'descripcion']),
            'universidadesSugeridas' => $this->getUniversidadesSugeridas(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Participante $participante)
    {
        $validated = $request->validate([
            'carnet' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('PARTICIPANTE', 'email')->ignore($participante->id)
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
            'universidad' => ['nullable', 'string', 'max:255'],
            'tipo_participante_id' => ['required', 'exists:TIPO_PARTICIPANTE,id'],
            'activo' => ['boolean'],
        ], [
            'email.unique' => 'Ya existe otro participante con este correo electrónico.',
        ]);

        // Verificar duplicado por carnet y tipo (excluyendo el actual)
        $duplicado = Participante::where('carnet', $validated['carnet'])
            ->where('tipo_participante_id', $validated['tipo_participante_id'])
            ->where('id', '!=', $participante->id)
            ->first();

        if ($duplicado) {
            return back()->withErrors([
                'carnet' => 'Ya existe otro participante con este carnet para el tipo seleccionado.'
            ])->withInput();
        }

        $participante->update($validated);

        return redirect()->route('administrativo.participantes.index')
            ->with('success', 'Participante actualizado exitosamente.');
    }

    /**
     * Desactivar participante (soft delete)
     */
    public function desactivar(Participante $participante)
    {
        // Verificar si tiene inscripciones activas
        $inscripcionesActivas = $participante->inscripciones()
            ->where('estado', '!=', 'RETIRADO')
            ->count();

        if ($inscripcionesActivas > 0) {
            return back()->with(
                'error',
                "No se puede desactivar el participante porque tiene {$inscripcionesActivas} inscripción(es) activa(s)."
            );
        }

        $participante->update(['activo' => false]);

        return back()->with('success', 'Participante desactivado exitosamente.');
    }

    /**
     * Reactivar participante
     */
    public function reactivar(Participante $participante)
    {
        $participante->update(['activo' => true]);

        return back()->with('success', 'Participante reactivado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(Participante $participante)
    {
        if ($participante->activo) {
            return $this->desactivar($participante);
        } else {
            return $this->reactivar($participante);
        }
    }

    /**
     * Obtener preinscripciones del participante
     */
    public function getPreinscripciones(Participante $participante)
    {
        $preinscripciones = $participante->preinscripciones()
            ->with(['curso.tutor', 'pago'])
            ->orderBy('fecha_preinscripcion', 'desc')
            ->get()
            ->map(function ($preinscripcion) {
                return [
                    'id' => $preinscripcion->id,
                    'curso' => $preinscripcion->curso->nombre,
                    'tutor' => $preinscripcion->curso->tutor->nombre_completo,
                    'fecha_preinscripcion' => $preinscripcion->fecha_preinscripcion->format('d/m/Y H:i'),
                    'estado' => $preinscripcion->estado,
                    'tiene_pago' => $preinscripcion->tienePago(),
                    'monto_pago' => $preinscripcion->pago?->monto_formateado,
                    'observaciones' => $preinscripcion->observaciones,
                ];
            });

        return response()->json($preinscripciones);
    }

    /**
     * Buscar participantes para autocompletado
     */
    public function buscar(Request $request)
    {
        $termino = $request->input('q', '');

        if (strlen($termino) < 2) {
            return response()->json([]);
        }

        $participantes = Participante::activo()
            ->where(function ($query) use ($termino) {
                $query->where('carnet', 'ILIKE', "%{$termino}%")
                    ->orWhere('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                    ->orWhere('email', 'ILIKE', "%{$termino}%");
            })
            ->with('tipoParticipante')
            ->take(10)
            ->get()
            ->map(function ($participante) {
                return [
                    'id' => $participante->id,
                    'carnet' => $participante->carnet,
                    'nombre_completo' => $participante->nombre_completo,
                    'email' => $participante->email,
                    'tipo' => $participante->tipoParticipante->codigo,
                    'registro' => $participante->registro,
                ];
            });

        return response()->json($participantes);
    }

    /**
     * Exportar lista de participantes
     */
    public function exportar(Request $request)
    {
        $participantes = Participante::with(['tipoParticipante'])
            ->when($request->tipo_participante_id, function ($query, $tipoId) {
                $query->where('tipo_participante_id', $tipoId);
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->get()
            ->map(function ($participante) {
                return [
                    'registro' => $participante->registro,
                    'carnet' => $participante->carnet,
                    'nombre_completo' => $participante->nombre_completo,
                    'email' => $participante->email,
                    'telefono' => $participante->telefono,
                    'universidad' => $participante->universidad,
                    'tipo' => $participante->tipoParticipante->descripcion,
                    'activo' => $participante->activo ? 'Sí' : 'No',
                    'fecha_registro' => $participante->created_at->format('d/m/Y'),
                ];
            });

        return response()->json($participantes)
            ->header('Content-Disposition', 'attachment; filename="participantes_cicit.json"');
    }

    /**
     * Estadísticas para dashboard administrativo
     */
    public function estadisticas()
    {
        $stats = [
            'total_participantes' => Participante::count(),
            'participantes_activos' => Participante::activo()->count(),
            'nuevos_este_mes' => Participante::whereMonth('created_at', now()->month)->count(),
            'con_preinscripciones_pendientes' => Participante::whereHas('preinscripciones', function ($query) {
                $query->where('estado', 'PENDIENTE');
            })->count(),
            'distribucion_por_tipo' => TipoParticipante::withCount('participantes')->get(['codigo', 'participantes_count']),
            'actividad_reciente' => Participante::latest('updated_at')->take(5)->get(['carnet', 'nombre', 'apellido', 'updated_at']),
        ];

        return response()->json($stats);
    }

    /**
     * Generar registro único para participante
     */
    private function generarRegistroParticipante($datos)
    {
        $año = Carbon::now()->year;
        $tipo = TipoParticipante::find($datos['tipo_participante_id'])->codigo;
        $numero = Participante::where('tipo_participante_id', $datos['tipo_participante_id'])
            ->whereYear('created_at', $año)
            ->count() + 1;

        return "{$tipo}-{$año}-" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener lista de universidades únicas
     */
    private function getUniversidadesList()
    {
        return Participante::whereNotNull('universidad')
            ->distinct()
            ->orderBy('universidad')
            ->pluck('universidad')
            ->filter()
            ->take(20);
    }

    /**
     * Obtener universidades sugeridas
     */
    private function getUniversidadesSugeridas()
    {
        return [
            'Universidad Autónoma Gabriel René Moreno (UAGRM)',
            'Universidad Privada de Santa Cruz (UPSA)',
            'Universidad Católica Boliviana San Pablo',
            'Universidad NUR',
            'Universidad Evangélica Boliviana',
            'Universidad Franz Tamayo (UNIFRANZ)',
            'Universidad del Valle',
            'Universidad Tecnológica Boliviana',
        ];
    }
}
