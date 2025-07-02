<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Preinscripcion;
use App\Models\Participante;
use App\Models\Curso;
use App\Models\TipoParticipante;
use App\Models\VisitaPagina;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PreinscripcionPublicaController extends Controller
{
    /**
     * Mostrar formulario de preinscripción
     */
    public function create(Request $request, $cursoId = null)
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/preinscripcion', $request);

        // Si viene con curso específico, verificar que esté disponible
        $cursoSeleccionado = null;
        if ($cursoId) {
            $cursoSeleccionado = Curso::activo()
                ->with(['tutor', 'precios.tipoParticipante'])
                ->find($cursoId);

            if (!$cursoSeleccionado || !$this->cursoDisponibleParaPreinscripcion($cursoSeleccionado)) {
                return redirect('/cursos')
                    ->with('error', 'El curso seleccionado no está disponible para preinscripción.');
            }
        }

        // Obtener cursos disponibles para preinscripción
        $cursosDisponibles = Curso::activo()
            ->with(['tutor', 'precios.tipoParticipante'])
            ->where('fecha_inicio', '>', Carbon::now()->addDays(3)) // Al menos 3 días de anticipación
            ->whereRaw('"cupos_ocupados" < "cupos_totales"')
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'tutor' => $curso->tutor->nombre_completo,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'cupos_disponibles' => $curso->cupos_disponibles,
                    'precios' => $curso->precios->map(function ($precio) {
                        return [
                            'tipo' => $precio->tipoParticipante->codigo,
                            'descripcion' => $precio->tipoParticipante->descripcion,
                            'precio' => $precio->precio_formateado,
                        ];
                    }),
                ];
            });

        // Tipos de participante
        $tiposParticipante = TipoParticipante::activo()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'descripcion']);

        return Inertia::render('Publico/Preinscripcion/Create', [
            'cursosDisponibles' => $cursosDisponibles,
            'cursoSeleccionado' => $cursoSeleccionado,
            'tiposParticipante' => $tiposParticipante,
            'contadorVisitas' => VisitaPagina::getTextoContador('/preinscripcion'),
        ]);
    }

    /**
     * Procesar preinscripción
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Datos del participante
            'carnet' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'universidad' => ['nullable', 'string', 'max:255'],
            'tipo_participante_id' => ['required', 'exists:TIPO_PARTICIPANTE,id'],

            // Curso seleccionado
            'curso_id' => ['required', 'exists:CURSO,id'],

            // Términos y condiciones
            'acepta_terminos' => ['required', 'accepted'],
            'acepta_datos' => ['required', 'accepted'],
        ], [
            // Mensajes en español (Requisito 6)
            'carnet.required' => 'El número de carnet es obligatorio.',
            'carnet.max' => 'El carnet no puede tener más de 20 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.max' => 'El apellido no puede tener más de 100 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe proporcionar un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede tener más de 255 caracteres.',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'universidad.max' => 'El nombre de la universidad no puede tener más de 255 caracteres.',
            'tipo_participante_id.required' => 'Debe seleccionar su tipo de participante.',
            'tipo_participante_id.exists' => 'El tipo de participante seleccionado no es válido.',
            'curso_id.required' => 'Debe seleccionar un curso.',
            'curso_id.exists' => 'El curso seleccionado no es válido.',
            'acepta_terminos.required' => 'Debe aceptar los términos y condiciones.',
            'acepta_terminos.accepted' => 'Debe aceptar los términos y condiciones.',
            'acepta_datos.required' => 'Debe autorizar el tratamiento de datos personales.',
            'acepta_datos.accepted' => 'Debe autorizar el tratamiento de datos personales.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Verificar que el curso sigue disponible
                $curso = Curso::lockForUpdate()->find($validated['curso_id']);

                if (!$this->cursoDisponibleParaPreinscripcion($curso)) {
                    throw new \Exception('El curso ya no está disponible para preinscripción.');
                }

                // Buscar o crear participante
                $participante = Participante::where('carnet', $validated['carnet'])
                    ->where('email', $validated['email'])
                    ->first();

                if ($participante) {
                    // Actualizar datos del participante existente
                    $participante->update([
                        'nombre' => $validated['nombre'],
                        'apellido' => $validated['apellido'],
                        'telefono' => $validated['telefono'],
                        'universidad' => $validated['universidad'],
                        'tipo_participante_id' => $validated['tipo_participante_id'],
                        'activo' => true,
                    ]);
                } else {
                    // Crear nuevo participante
                    $participante = Participante::create([
                        'carnet' => $validated['carnet'],
                        'nombre' => $validated['nombre'],
                        'apellido' => $validated['apellido'],
                        'email' => $validated['email'],
                        'telefono' => $validated['telefono'],
                        'universidad' => $validated['universidad'],
                        'tipo_participante_id' => $validated['tipo_participante_id'],
                        'activo' => true,
                        'registro' => $this->generarRegistroParticipante($validated),
                    ]);
                }

                // Verificar que no exista preinscripción previa
                $preinscripcionExistente = Preinscripcion::where('participante_id', $participante->id)
                    ->where('curso_id', $validated['curso_id'])
                    ->first();

                if ($preinscripcionExistente) {
                    throw new \Exception('Ya existe una preinscripción para este participante en este curso.');
                }

                // Crear preinscripción
                $preinscripcion = Preinscripcion::create([
                    'participante_id' => $participante->id,
                    'curso_id' => $validated['curso_id'],
                    'fecha_preinscripcion' => now(),
                    'estado' => 'PENDIENTE',
                    'observaciones' => 'Preinscripción realizada desde formulario público',
                ]);

                // Guardar información para mostrar en la respuesta
                $this->preinscripcionCreada = $preinscripcion;
                $this->participanteCreado = $participante;
            });

            // Obtener información para mostrar confirmación
            $curso = Curso::with(['tutor', 'precios.tipoParticipante'])
                ->find($validated['curso_id']);

            $precioAplicable = $curso->precios()
                ->where('tipo_participante_id', $validated['tipo_participante_id'])
                ->first();

            return redirect('/preinscripcion/confirmacion')
                ->with('success', 'Preinscripción realizada exitosamente.')
                ->with('preinscripcion_data', [
                    'id' => $this->preinscripcionCreada->id,
                    'participante' => $this->participanteCreado->nombre_completo,
                    'carnet' => $this->participanteCreado->carnet,
                    'email' => $this->participanteCreado->email,
                    'telefono' => $this->participanteCreado->telefono,
                    'universidad' => $this->participanteCreado->universidad,
                    'curso' => $curso->nombre,
                    'tutor' => $curso->tutor->nombre_completo,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'fecha_fin' => $curso->fecha_fin->format('d/m/Y'),
                    'duracion_horas' => $curso->duracion_horas,
                    'aula' => $curso->aula,
                    'precio' => $precioAplicable->precio_formateado ?? 'No definido',
                    'tipo_participante' => $precioAplicable->tipoParticipante->descripcion ?? '',
                    'fecha_preinscripcion' => $this->preinscripcionCreada->fecha_preinscripcion->format('d/m/Y H:i'),
                ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Página de confirmación de preinscripción
     */
    public function confirmacion(Request $request)
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/preinscripcion/confirmacion', $request);

        $datosPreinscripcion = session('preinscripcion_data');

        if (!$datosPreinscripcion) {
            return redirect('/cursos')
                ->with('info', 'No hay información de preinscripción para mostrar.');
        }

        return Inertia::render('Publico/Preinscripcion/Confirmacion', [
            'datos' => $datosPreinscripcion,
            'contadorVisitas' => VisitaPagina::getTextoContador('/preinscripcion/confirmacion'),
            'pasos_siguientes' => $this->getPasosSiguientes(),
        ]);
    }

    /**
     * Consultar estado de preinscripción
     */
    public function consultar(Request $request): Response
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/preinscripcion/consultar', $request);

        return Inertia::render('Publico/Preinscripcion/Consultar', [
            'contadorVisitas' => VisitaPagina::getTextoContador('/preinscripcion/consultar'),
        ]);
    }

    /**
     * Buscar preinscripción por datos
     */
    public function buscarPreinscripcion(Request $request)
    {
        $validated = $request->validate([
            'carnet' => ['required', 'string'],
            'email' => ['required', 'email'],
        ], [
            'carnet.required' => 'El número de carnet es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe proporcionar un correo electrónico válido.',
        ]);

        // Buscar participante
        $participante = Participante::where('carnet', $validated['carnet'])
            ->where('email', $validated['email'])
            ->first();

        if (!$participante) {
            return response()->json([
                'encontrado' => false,
                'mensaje' => 'No se encontró ningún participante con esos datos.'
            ], 404);
        }

        // Obtener preinscripciones del participante
        $preinscripciones = Preinscripcion::where('participante_id', $participante->id)
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
                    'estado_descripcion' => $this->getDescripcionEstado($preinscripcion->estado),
                    'tiene_pago' => $preinscripcion->tienePago(),
                    'observaciones' => $preinscripcion->observaciones,
                    'precio_aplicable' => $preinscripcion->getPrecioAplicable(),
                ];
            });

        return response()->json([
            'encontrado' => true,
            'participante' => [
                'nombre_completo' => $participante->nombre_completo,
                'carnet' => $participante->carnet,
                'email' => $participante->email,
            ],
            'preinscripciones' => $preinscripciones,
        ]);
    }

    /**
     * Obtener cursos disponibles por AJAX
     */
    public function cursosDisponibles()
    {
        $cursos = Curso::activo()
            ->with(['tutor', 'precios.tipoParticipante'])
            ->where('fecha_inicio', '>', Carbon::now()->addDays(3))
            ->whereRaw('"cupos_ocupados" < "cupos_totales"')
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'descripcion' => substr($curso->descripcion, 0, 100) . '...',
                    'tutor' => $curso->tutor->nombre_completo,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'cupos_disponibles' => $curso->cupos_disponibles,
                    'aula' => $curso->aula,
                    'modalidad' => $curso->aula ? 'Presencial' : 'Virtual',
                    'precios' => $curso->precios->map(function ($precio) {
                        return [
                            'tipo_id' => $precio->tipo_participante_id,
                            'tipo' => $precio->tipoParticipante->codigo,
                            'descripcion' => $precio->tipoParticipante->descripcion,
                            'precio' => $precio->precio,
                            'precio_formateado' => $precio->precio_formateado,
                        ];
                    }),
                ];
            });

        return response()->json($cursos);
    }

    /**
     * Validar disponibilidad antes de mostrar formulario
     */
    public function validarDisponibilidad(Request $request)
    {
        $cursoId = $request->input('curso_id');
        $carnet = $request->input('carnet');
        $email = $request->input('email');

        $curso = Curso::activo()->find($cursoId);

        if (!$curso) {
            return response()->json([
                'disponible' => false,
                'mensaje' => 'Curso no encontrado.'
            ]);
        }

        if (!$this->cursoDisponibleParaPreinscripcion($curso)) {
            return response()->json([
                'disponible' => false,
                'mensaje' => 'El curso no está disponible para preinscripción.'
            ]);
        }

        // Verificar si ya existe preinscripción
        if ($carnet && $email) {
            $participante = Participante::where('carnet', $carnet)
                ->where('email', $email)
                ->first();

            if ($participante) {
                $preinscripcionExistente = Preinscripcion::where('participante_id', $participante->id)
                    ->where('curso_id', $cursoId)
                    ->exists();

                if ($preinscripcionExistente) {
                    return response()->json([
                        'disponible' => false,
                        'mensaje' => 'Ya tiene una preinscripción para este curso.'
                    ]);
                }
            }
        }

        return response()->json([
            'disponible' => true,
            'cupos_disponibles' => $curso->cupos_disponibles,
            'fecha_limite' => $curso->fecha_inicio->subDays(3)->format('d/m/Y'),
        ]);
    }

    /**
     * Verificar si curso está disponible para preinscripción
     */
    private function cursoDisponibleParaPreinscripcion($curso)
    {
        if (!$curso || !$curso->activo) {
            return false;
        }

        // Debe tener cupos disponibles
        if ($curso->cupos_disponibles <= 0) {
            return false;
        }

        // Debe iniciar al menos en 3 días
        if ($curso->fecha_inicio <= Carbon::now()->addDays(3)) {
            return false;
        }

        return true;
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
     * Obtener descripción del estado de preinscripción
     */
    private function getDescripcionEstado($estado)
    {
        return match ($estado) {
            'PENDIENTE' => 'Pendiente de revisión',
            'APROBADA' => 'Aprobada - Proceder con el pago',
            'RECHAZADA' => 'Rechazada',
            default => $estado
        };
    }

    /**
     * Obtener pasos siguientes después de la preinscripción
     */
    private function getPasosSiguientes()
    {
        return [
            [
                'numero' => 1,
                'titulo' => 'Revisión de Preinscripción',
                'descripcion' => 'El personal administrativo revisará su solicitud en un plazo de 24-48 horas.',
                'estado' => 'pendiente'
            ],
            [
                'numero' => 2,
                'titulo' => 'Notificación de Aprobación',
                'descripcion' => 'Recibirá un correo electrónico con la confirmación y detalles de pago.',
                'estado' => 'pendiente'
            ],
            [
                'numero' => 3,
                'titulo' => 'Realizar Pago',
                'descripcion' => 'Complete el pago según las instrucciones recibidas.',
                'estado' => 'pendiente'
            ],
            [
                'numero' => 4,
                'titulo' => 'Inscripción Oficial',
                'descripción' => 'Una vez confirmado el pago, será inscrito oficialmente al curso.',
                'estado' => 'pendiente'
            ]
        ];
    }

    /**
     * Variables para almacenar datos de la transacción
     */
    private $preinscripcionCreada;
    private $participanteCreado;

    /**
     * Generar PDF de la preinscripción
     */
    public function generarPDF($preinscripcionId)
    {
        try {
            // Buscar la preinscripción con relaciones
            $preinscripcion = Preinscripcion::with([
                'participante.tipoParticipante',
                'curso.tutor',
                'curso.precios.tipoParticipante'
            ])->findOrFail($preinscripcionId);

            // Obtener el precio aplicable
            $precioAplicable = $preinscripcion->curso->precios()
                ->where('tipo_participante_id', $preinscripcion->participante->tipo_participante_id)
                ->first();

            // Preparar datos para la vista
            $datos = [
                'preinscripcion' => $preinscripcion,
                'participante' => $preinscripcion->participante,
                'curso' => $preinscripcion->curso,
                'tutor' => $preinscripcion->curso->tutor,
                'precio' => $precioAplicable,
                'fecha_generacion' => now()->format('d/m/Y H:i'),
            ];

            // Generar HTML para el PDF
            $html = view('pdf.preinscripcion', $datos)->render();

            // Generar PDF usando Laravel DomPDF
            $pdf = Pdf::loadHtml($html)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => true,
                ]);

            // Nombre del archivo
            $nombreArchivo = 'preinscripcion_' . $preinscripcion->id . '_' . 
                            str_replace(' ', '_', $preinscripcion->participante->nombre) . '_' .
                            str_replace(' ', '_', $preinscripcion->participante->apellido) . '.pdf';

            // Devolver el PDF para descarga
            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'pdf' => 'Error al generar el PDF: ' . $e->getMessage()
            ]);
        }
    }
}
