<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Gestion;
use App\Models\TipoParticipante;
use App\Models\PrecioCurso;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CursoController extends Controller
{
    /**
     * Subir imagen del curso
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'], // máximo 2MB
        ], [
            'image.required' => 'Debe seleccionar una imagen.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png o webp.',
            'image.max' => 'La imagen no debe superar los 2MB.',
        ]);

        try {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Crear directorio si no existe
            $directory = 'public/cursos/logos';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Guardar imagen
            $path = $image->storeAs($directory, $filename);
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Curso::with(['tutor', 'gestion', 'precios.tipoParticipante']);

            // Aplicar filtros de búsqueda
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%")
                      ->orWhereHas('tutor', function ($tutor) use ($search) {
                          $tutor->where('nombre', 'LIKE', "%{$search}%")
                                ->orWhere('apellido', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Aplicar filtro de tutor
            if ($request->tutor_id) {
                $query->where('tutor_id', $request->tutor_id);
            }

            // Aplicar filtro de gestión
            if ($request->gestion_id) {
                $query->where('gestion_id', $request->gestion_id);
            }

            // Aplicar filtro de estado
            if ($request->activo !== null) {
                $query->where('activo', $request->boolean('activo'));
            }

            // Obtener los resultados
            $cursosData = $query
                ->withCount(['inscripciones', 'preinscripciones'])
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString();

            // Get support data
            $tutores = Usuario::where('rol', 'TUTOR')->where('activo', true)->get(['id', 'nombre', 'apellido']);
            $gestiones = Gestion::where('activo', true)->get(['id', 'nombre']);
            $tiposParticipante = TipoParticipante::where('activo', true)->get(['id', 'codigo', 'descripcion']);

            $estadisticas = [
                'total' => Curso::count(),
                'activos' => Curso::where('activo', true)->count(),
                'en_progreso' => Curso::where('activo', true)
                    ->where('fecha_inicio', '<=', now())
                    ->where('fecha_fin', '>=', now())
                    ->count(),
                'con_inscripciones' => Curso::has('inscripciones')->count(),
            ];

            // Para peticiones AJAX del componente, devolver JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'cursosData' => $cursosData,
                    'tutores' => $tutores,
                    'gestiones' => $gestiones,
                    'tiposParticipante' => $tiposParticipante,
                    'cursosEstadisticas' => $estadisticas,
                    'filters' => $request->only(['search', 'tutor_id', 'gestion_id', 'activo'])
                ]);
            }

            return redirect()->route('responsable.dashboard');
        } catch (\Exception $e) {
            Log::error('Error en CursoController@index: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Error al cargar los cursos',
                    'message' => $e->getMessage(),
                    'cursosData' => ['data' => []],
                    'tutores' => [],
                    'gestiones' => [],
                    'tiposParticipante' => [],
                    'cursosEstadisticas' => ['total' => 0, 'activos' => 0, 'en_progreso' => 0, 'con_inscripciones' => 0]
                ], 500);
            }

            return back()->with('error', 'Error al cargar los cursos');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Responsable/Cursos/Create', [
            'tutores' => Usuario::where('rol', 'TUTOR')->where('activo', true)->get(['id', 'nombre', 'apellido']),
            'gestiones' => Gestion::where('activo', true)->get(['id', 'nombre', 'fecha_inicio', 'fecha_fin']),
            'tiposParticipante' => TipoParticipante::where('activo', true)->get(['id', 'codigo', 'descripcion']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'duracion_horas' => ['required', 'integer', 'min:1'],
            'nivel' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'tutor_id' => ['required', 'exists:USUARIO,id'],
            'gestion_id' => ['required', 'exists:GESTION,id'],
            'aula' => ['nullable', 'string', 'max:50'],
            'cupos_totales' => ['required', 'integer', 'min:1'],
            'fecha_inicio' => ['required', 'date', 'before:fecha_fin'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'precios' => ['required', 'array', 'min:1'],
            'precios.*.tipo_participante_id' => ['required', 'exists:TIPO_PARTICIPANTE,id'],
            'precios.*.precio' => ['required', 'numeric', 'min:0'],
        ], [
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'duracion_horas.required' => 'La duración en horas es obligatoria.',
            'duracion_horas.min' => 'La duración debe ser mayor a 0 horas.',
            'tutor_id.required' => 'Debe seleccionar un tutor.',
            'tutor_id.exists' => 'El tutor seleccionado no es válido.',
            'gestion_id.required' => 'Debe seleccionar una gestión.',
            'gestion_id.exists' => 'La gestión seleccionada no es válida.',
            'cupos_totales.required' => 'Los cupos totales son obligatorios.',
            'cupos_totales.min' => 'Debe haber al menos 1 cupo disponible.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.before' => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'precios.required' => 'Debe configurar al menos un precio.',
            'precios.*.tipo_participante_id.required' => 'Debe seleccionar el tipo de participante.',
            'precios.*.precio.required' => 'El precio es obligatorio.',
            'precios.*.precio.min' => 'El precio debe ser mayor o igual a 0.',
        ]);

        DB::transaction(function () use ($validated) {
            // Verificar que el tutor esté activo
            $tutor = Usuario::find($validated['tutor_id']);
            if (!$tutor->activo) {
                throw new \Exception('El tutor seleccionado no está activo.');
            }

            // Crear el curso
            $curso = Curso::create([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'duracion_horas' => $validated['duracion_horas'],
                'nivel' => $validated['nivel'],
                'logo_url' => $validated['logo_url'],
                'tutor_id' => $validated['tutor_id'],
                'gestion_id' => $validated['gestion_id'],
                'aula' => $validated['aula'],
                'cupos_totales' => $validated['cupos_totales'],
                'cupos_ocupados' => 0,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'activo' => true,
            ]);

            // Crear los precios por tipo de participante
            foreach ($validated['precios'] as $precioData) {
                PrecioCurso::create([
                    'curso_id' => $curso->id,
                    'tipo_participante_id' => $precioData['tipo_participante_id'],
                    'precio' => $precioData['precio'],
                    'activo' => true,
                ]);
            }
        });

        return redirect('/dashboard')
            ->with('success', 'Curso creado exitosamente.')
            ->with('activeSection', 'cursos');
    }

    /**
     * Display the specified resource.
     */
    public function show(Curso $curso): Response
    {
        $curso->load([
            'tutor',
            'gestion',
            'precios.tipoParticipante',
            'inscripciones.participante',
            'preinscripciones.participante',
            'tareas'
        ]);

        // Estadísticas del curso
        $estadisticas = [
            'cupos_disponibles' => $curso->cupos_disponibles,
            'porcentaje_ocupacion' => $curso->porcentaje_ocupacion,
            'total_preinscripciones' => $curso->preinscripciones->count(),
            'preinscripciones_pendientes' => $curso->preinscripciones->where('estado', 'PENDIENTE')->count(),
            'inscripciones_activas' => $curso->inscripciones->where('estado', '!=', 'RETIRADO')->count(),
            'total_tareas' => $curso->tareas->count(),
            'promedio_asistencia' => $this->calcularPromedioAsistencia($curso),
            'ingresos_generados' => $this->calcularIngresosCurso($curso),
        ];

        return Inertia::render('Responsable/Cursos/Show', [
            'curso' => $curso,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curso $curso): Response
    {
        $curso->load('precios.tipoParticipante');

        return Inertia::render('Responsable/Cursos/Edit', [
            'curso' => $curso,
            'tutores' => Usuario::where('rol', 'TUTOR')->where('activo', true)->get(['id', 'nombre', 'apellido']),
            'gestiones' => Gestion::where('activo', true)->get(['id', 'nombre', 'fecha_inicio', 'fecha_fin']),
            'tiposParticipante' => TipoParticipante::where('activo', true)->get(['id', 'codigo', 'descripcion']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curso $curso)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'duracion_horas' => ['required', 'integer', 'min:1'],
            'nivel' => ['nullable', 'string', 'max:50'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'tutor_id' => ['required', 'exists:USUARIO,id'],
            'gestion_id' => ['required', 'exists:GESTION,id'],
            'aula' => ['nullable', 'string', 'max:50'],
            'cupos_totales' => ['required', 'integer', 'min:' . $curso->cupos_ocupados],
            'fecha_inicio' => ['required', 'date', 'before:fecha_fin'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
            'activo' => ['boolean'],
            'precios' => ['required', 'array', 'min:1'],
            'precios.*.tipo_participante_id' => ['required', 'exists:TIPO_PARTICIPANTE,id'],
            'precios.*.precio' => ['required', 'numeric', 'min:0'],
        ], [
            'cupos_totales.min' => "Los cupos totales no pueden ser menores a los cupos ocupados ({$curso->cupos_ocupados}).",
        ]);

        DB::transaction(function () use ($validated, $curso) {
            // Actualizar el curso
            $curso->update([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'duracion_horas' => $validated['duracion_horas'],
                'nivel' => $validated['nivel'],
                'logo_url' => $validated['logo_url'],
                'tutor_id' => $validated['tutor_id'],
                'gestion_id' => $validated['gestion_id'],
                'aula' => $validated['aula'],
                'cupos_totales' => $validated['cupos_totales'],
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'activo' => $validated['activo'] ?? $curso->activo,
            ]);

            // Actualizar precios - eliminar existentes y crear nuevos
            $curso->precios()->delete();

            foreach ($validated['precios'] as $precioData) {
                PrecioCurso::create([
                    'curso_id' => $curso->id,
                    'tipo_participante_id' => $precioData['tipo_participante_id'],
                    'precio' => $precioData['precio'],
                    'activo' => true,
                ]);
            }
        });

        return redirect('/dashboard')
            ->with('success', 'Curso actualizado exitosamente.')
            ->with('activeSection', 'cursos');
    }

    /**
     * Desactivar curso (soft delete)
     */
    public function desactivar(Curso $curso)
    {
        // Verificar si tiene inscripciones activas
        $inscripcionesActivas = $curso->inscripciones()
            ->where('estado', '!=', 'RETIRADO')
            ->count();

        if ($inscripcionesActivas > 0) {
            return back()->with(
                'error',
                "No se puede desactivar el curso porque tiene {$inscripcionesActivas} inscripción(es) activa(s)."
            );
        }

        $curso->update(['activo' => false]);

        return back()->with('success', 'Curso desactivado exitosamente.');
    }

    /**
     * Reactivar curso
     */
    public function reactivar(Curso $curso)
    {
        $curso->update(['activo' => true]);

        return back()->with('success', 'Curso reactivado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(Curso $curso)
    {
        if ($curso->activo) {
            return $this->desactivar($curso);
        } else {
            return $this->reactivar($curso);
        }
    }

    /**
     * Obtener cursos activos para selects
     */
    public function getCursosActivos()
    {
        $cursos = Curso::where('activo', true)
            ->with('tutor')
            ->select('id', 'nombre', 'tutor_id', 'cupos_totales', 'cupos_ocupados')
            ->get()
            ->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'tutor' => $curso->tutor->nombre . ' ' . $curso->tutor->apellido,
                    'cupos_disponibles' => $curso->cupos_totales - ($curso->cupos_ocupados ?? 0),
                ];
            });

        return response()->json($cursos);
    }

    /**
     * Estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_cursos' => Curso::count(),
            'cursos_activos' => Curso::where('activo', true)->count(),
            'en_progreso' => Curso::where('activo', true)
                ->where('fecha_inicio', '<=', now())
                ->where('fecha_fin', '>=', now())
                ->count(),
            'promedio_cupos_ocupacion' => Curso::where('activo', true)->avg('cupos_ocupados'),
            'cursos_por_tutor' => Usuario::where('rol', 'TUTOR')
                ->withCount('cursos')
                ->having('cursos_count', '>', 0)
                ->get(['nombre', 'apellido', 'cursos_count']),
        ];

        return response()->json($stats);
    }

    /**
     * Calcular promedio de asistencia del curso
     */
    private function calcularPromedioAsistencia(Curso $curso)
    {
        $inscripciones = $curso->inscripciones;

        if ($inscripciones->isEmpty()) {
            return 0;
        }

        $totalPorcentajes = 0;

        foreach ($inscripciones as $inscripcion) {
            $totalPorcentajes += $inscripcion->porcentajeAsistencia();
        }

        return round($totalPorcentajes / $inscripciones->count(), 2);
    }

    /**
     * Calcular ingresos generados por el curso
     */
    private function calcularIngresosCurso(Curso $curso)
    {
        return $curso->inscripciones()
            ->whereHas('preinscripcion.pago')
            ->get()
            ->sum(function ($inscripcion) {
                return $inscripcion->preinscripcion->pago->monto ?? 0;
            });
    }
}
