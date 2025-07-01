<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\Participante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificadoController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede gestionar certificados
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
        $certificados = Certificado::with(['inscripcion.participante', 'inscripcion.curso.tutor'])
            ->when($request->search, function ($query, $search) {
                $query->where('codigo_verificacion', 'ILIKE', "%{$search}%")
                    ->orWhereHas('inscripcion.participante', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%")
                            ->orWhere('apellido', 'ILIKE', "%{$search}%")
                            ->orWhere('carnet', 'ILIKE', "%{$search}%");
                    })
                    ->orWhereHas('inscripcion.curso', function ($q) use ($search) {
                        $q->where('nombre', 'ILIKE', "%{$search}%");
                    });
            })
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->whereHas('inscripcion', function ($q) use ($cursoId) {
                    $q->where('curso_id', $cursoId);
                });
            })
            ->when($request->fecha_desde, function ($query, $fecha) {
                $query->whereDate('fecha_emision', '>=', $fecha);
            })
            ->when($request->fecha_hasta, function ($query, $fecha) {
                $query->whereDate('fecha_emision', '<=', $fecha);
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Responsable/Certificados/Index', [
            'certificados' => $certificados,
            'filters' => $request->only(['search', 'tipo', 'curso_id', 'fecha_desde', 'fecha_hasta']),
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'tipos' => [
                'PARTICIPACION' => 'Certificado de Participación',
                'APROBACION' => 'Certificado de Aprobación',
                'MENCION_HONOR' => 'Certificado con Mención de Honor'
            ],
            'estadisticas' => [
                'total' => Certificado::count(),
                'este_mes' => Certificado::whereMonth('fecha_emision', now()->month)->count(),
                'participacion' => Certificado::participacion()->count(),
                'aprobacion' => Certificado::aprobacion()->count(),
                'mencion_honor' => Certificado::mencionHonor()->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Obtener inscripciones que pueden recibir certificado
        $inscripcionesElegibles = Inscripcion::with(['participante', 'curso'])
            ->whereIn('estado', ['APROBADO', 'INSCRITO'])
            ->whereDoesntHave('certificados') // Que no tengan certificado aún
            ->when($request->curso_id, function ($query, $cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->get();

        return Inertia::render('Responsable/Certificados/Create', [
            'inscripcionesElegibles' => $inscripcionesElegibles,
            'cursos' => Curso::activo()->get(['id', 'nombre']),
            'tipos' => [
                'PARTICIPACION' => 'Certificado de Participación',
                'APROBACION' => 'Certificado de Aprobación',
                'MENCION_HONOR' => 'Certificado con Mención de Honor'
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inscripcion_id' => ['required', 'exists:INSCRIPCION,id'],
            'tipo' => ['required', Rule::in(['PARTICIPACION', 'APROBACION', 'MENCION_HONOR'])],
            'fecha_emision' => ['nullable', 'date'],
        ], [
            'inscripcion_id.required' => 'Debe seleccionar una inscripción.',
            'inscripcion_id.exists' => 'La inscripción seleccionada no es válida.',
            'tipo.required' => 'El tipo de certificado es obligatorio.',
            'tipo.in' => 'El tipo de certificado seleccionado no es válido.',
        ]);

        // Verificar que la inscripción no tenga ya un certificado
        $inscripcion = Inscripcion::find($validated['inscripcion_id']);

        if ($inscripcion->certificados()->exists()) {
            return back()->withErrors([
                'inscripcion_id' => 'Esta inscripción ya tiene un certificado emitido.'
            ])->withInput();
        }

        // Validar tipo de certificado según el estado de la inscripción
        if ($validated['tipo'] === 'APROBACION' && $inscripcion->estado !== 'APROBADO') {
            return back()->withErrors([
                'tipo' => 'No se puede emitir certificado de aprobación para una inscripción no aprobada.'
            ])->withInput();
        }

        if (
            $validated['tipo'] === 'MENCION_HONOR' &&
            ($inscripcion->estado !== 'APROBADO' || ($inscripcion->nota_final ?? 0) < 90)
        ) {
            return back()->withErrors([
                'tipo' => 'Certificado de mención de honor requiere inscripción aprobada con nota >= 90.'
            ])->withInput();
        }

        // Crear el certificado
        $certificado = Certificado::create([
            'inscripcion_id' => $validated['inscripcion_id'],
            'tipo' => $validated['tipo'],
            'fecha_emision' => $validated['fecha_emision'] ?? now()->toDateString(),
            // El código se genera automáticamente en el modelo
        ]);

        // Generar PDF del certificado
        $this->generarPDF($certificado);

        return redirect()->route('responsable.certificados.index')
            ->with('success', 'Certificado emitido exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificado $certificado): Response
    {
        $certificado->load([
            'inscripcion.participante.tipoParticipante',
            'inscripcion.curso.gestion',
            'inscripcion.curso.tutor'
        ]);

        return Inertia::render('Responsable/Certificados/Show', [
            'certificado' => $certificado,
            'informacion_completa' => $certificado->getInformacionCompleta(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificado $certificado): Response
    {
        $certificado->load('inscripcion.participante', 'inscripcion.curso');

        return Inertia::render('Responsable/Certificados/Edit', [
            'certificado' => $certificado,
            'tipos' => [
                'PARTICIPACION' => 'Certificado de Participación',
                'APROBACION' => 'Certificado de Aprobación',
                'MENCION_HONOR' => 'Certificado con Mención de Honor'
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Certificado $certificado)
    {
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['PARTICIPACION', 'APROBACION', 'MENCION_HONOR'])],
            'fecha_emision' => ['required', 'date'],
        ], [
            'tipo.required' => 'El tipo de certificado es obligatorio.',
            'tipo.in' => 'El tipo de certificado seleccionado no es válido.',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
        ]);

        // Validar tipo de certificado según el estado de la inscripción
        $inscripcion = $certificado->inscripcion;

        if ($validated['tipo'] === 'APROBACION' && $inscripcion->estado !== 'APROBADO') {
            return back()->withErrors([
                'tipo' => 'No se puede cambiar a certificado de aprobación para una inscripción no aprobada.'
            ])->withInput();
        }

        if (
            $validated['tipo'] === 'MENCION_HONOR' &&
            ($inscripcion->estado !== 'APROBADO' || ($inscripcion->nota_final ?? 0) < 90)
        ) {
            return back()->withErrors([
                'tipo' => 'Certificado de mención de honor requiere inscripción aprobada con nota >= 90.'
            ])->withInput();
        }

        $certificado->update($validated);

        // Regenerar PDF si cambió el tipo
        if ($certificado->wasChanged('tipo')) {
            $this->generarPDF($certificado);
        }

        return redirect()->route('responsable.certificados.index')
            ->with('success', 'Certificado actualizado exitosamente.');
    }

    /**
     * Generar certificados masivos para un curso
     */
    public function generarMasivo(Request $request)
    {
        $validated = $request->validate([
            'curso_id' => ['required', 'exists:CURSO,id'],
            'tipo' => ['required', Rule::in(['PARTICIPACION', 'APROBACION', 'MENCION_HONOR'])],
            'solo_aprobados' => ['boolean'],
        ], [
            'curso_id.required' => 'Debe seleccionar un curso.',
            'tipo.required' => 'El tipo de certificado es obligatorio.',
        ]);

        $curso = Curso::find($validated['curso_id']);

        // Obtener inscripciones elegibles
        $inscripciones = $curso->inscripciones()
            ->whereDoesntHave('certificados')
            ->when($validated['solo_aprobados'], function ($query) {
                $query->where('estado', 'APROBADO');
            })
            ->when($validated['tipo'] === 'MENCION_HONOR', function ($query) {
                $query->where('estado', 'APROBADO')
                    ->where('nota_final', '>=', 90);
            })
            ->get();

        if ($inscripciones->isEmpty()) {
            return back()->with('error', 'No hay inscripciones elegibles para certificación en este curso.');
        }

        $certificadosCreados = 0;

        foreach ($inscripciones as $inscripcion) {
            $certificado = Certificado::create([
                'inscripcion_id' => $inscripcion->id,
                'tipo' => $validated['tipo'],
                'fecha_emision' => now()->toDateString(),
            ]);

            $this->generarPDF($certificado);
            $certificadosCreados++;
        }

        return back()->with('success', "Se crearon {$certificadosCreados} certificados exitosamente.");
    }

    /**
     * Descargar PDF del certificado
     */
    public function descargarPDF(Certificado $certificado)
    {
        if (!$certificado->tiene_pdf) {
            $this->generarPDF($certificado);
        }

        $path = storage_path('app/public/' . $certificado->url_pdf);

        if (!file_exists($path)) {
            $this->generarPDF($certificado);
            $path = storage_path('app/public/' . $certificado->url_pdf);
        }

        return response()->download(
            $path,
            "Certificado_{$certificado->codigo_verificacion}.pdf"
        );
    }

    /**
     * Verificar certificado por código
     */
    public function verificar(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string'],
        ], [
            'codigo.required' => 'El código de verificación es obligatorio.',
        ]);

        $certificado = Certificado::where('codigo_verificacion', $validated['codigo'])
            ->with([
                'inscripcion.participante',
                'inscripcion.curso.tutor',
                'inscripcion.curso.gestion'
            ])
            ->first();

        if (!$certificado) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Código de verificación no encontrado.'
            ], 404);
        }

        return response()->json([
            'valido' => true,
            'certificado' => $certificado->getInformacionCompleta(),
        ]);
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_certificados' => Certificado::count(),
            'certificados_mes' => Certificado::whereMonth('fecha_emision', now()->month)->count(),
            'por_tipo' => [
                'participacion' => Certificado::participacion()->count(),
                'aprobacion' => Certificado::aprobacion()->count(),
                'mencion_honor' => Certificado::mencionHonor()->count(),
            ],
            'ultimos_emitidos' => Certificado::with(['inscripcion.participante', 'inscripcion.curso'])
                ->latest('fecha_emision')
                ->take(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Generar archivo PDF del certificado
     */
    private function generarPDF(Certificado $certificado)
    {
        $certificado->load([
            'inscripcion.participante.tipoParticipante',
            'inscripcion.curso.gestion',
            'inscripcion.curso.tutor'
        ]);

        $data = [
            'certificado' => $certificado,
            'participante' => $certificado->participante,
            'curso' => $certificado->curso,
            'tutor' => $certificado->curso->tutor,
            'gestion' => $certificado->curso->gestion,
        ];

        // Generar PDF usando una vista
        $pdf = Pdf::loadView('certificados.template', $data);

        // Definir ruta del archivo
        $filename = "certificados/{$certificado->codigo_verificacion}.pdf";
        $path = "public/{$filename}";

        // Guardar archivo
        Storage::put($path, $pdf->output());

        // Actualizar URL en el certificado
        $certificado->update([
            'url_pdf' => $filename
        ]);
    }

    /**
     * Obtener inscripciones elegibles para certificación
     */
    public function getInscripcionesElegibles(Request $request)
    {
        $cursoId = $request->input('curso_id');

        $inscripciones = Inscripcion::with(['participante', 'curso'])
            ->whereIn('estado', ['APROBADO', 'INSCRITO'])
            ->whereDoesntHave('certificados')
            ->when($cursoId, function ($query) use ($cursoId) {
                $query->where('curso_id', $cursoId);
            })
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'id' => $inscripcion->id,
                    'participante' => $inscripcion->participante->nombre_completo,
                    'curso' => $inscripcion->curso->nombre,
                    'estado' => $inscripcion->estado,
                    'nota_final' => $inscripcion->nota_final,
                    'puede_aprobacion' => $inscripcion->estado === 'APROBADO',
                    'puede_mencion' => $inscripcion->estado === 'APROBADO' && ($inscripcion->nota_final ?? 0) >= 90,
                ];
            });

        return response()->json($inscripciones);
    }
}
