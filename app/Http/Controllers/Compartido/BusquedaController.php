<?php

namespace App\Http\Controllers\Compartido;

use App\Http\Controllers\Controller;
use App\Models\BusquedaLog;
use App\Models\Curso;
use App\Models\Participante;
use App\Models\Usuario;
use App\Models\Certificado;
use App\Models\Gestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BusquedaController extends Controller
{
    /**
     * Realizar búsqueda general en el sistema
     */
    public function buscar(Request $request)
    {
        $validated = $request->validate([
            'termino' => ['required', 'string', 'min:2', 'max:255'],
            'tipo' => ['nullable', 'string', 'in:cursos,participantes,usuarios,certificados,todo'],
        ], [
            'termino.required' => 'El término de búsqueda es obligatorio.',
            'termino.min' => 'El término debe tener al menos 2 caracteres.',
            'termino.max' => 'El término no puede exceder 255 caracteres.',
            'tipo.in' => 'Tipo de búsqueda no válido.',
        ]);

        $termino = $validated['termino'];
        $tipo = $validated['tipo'] ?? 'todo';

        // Registrar la búsqueda en el log
        $this->registrarBusqueda($termino);

        $resultados = [];
        $totalResultados = 0;

        switch ($tipo) {
            case 'cursos':
                $resultados['cursos'] = $this->buscarCursos($termino);
                $totalResultados = $resultados['cursos']->count();
                break;

            case 'participantes':
                if (Auth::check() && in_array(Auth::user()->rol, ['RESPONSABLE', 'ADMINISTRATIVO'])) {
                    $resultados['participantes'] = $this->buscarParticipantes($termino);
                    $totalResultados = $resultados['participantes']->count();
                }
                break;

            case 'usuarios':
                if (Auth::check() && Auth::user()->rol === 'RESPONSABLE') {
                    $resultados['usuarios'] = $this->buscarUsuarios($termino);
                    $totalResultados = $resultados['usuarios']->count();
                }
                break;

            case 'certificados':
                $resultados['certificados'] = $this->buscarCertificados($termino);
                $totalResultados = $resultados['certificados']->count();
                break;

            default: // 'todo'
                $resultados = $this->buscarTodo($termino);
                $totalResultados = collect($resultados)->flatten()->count();
                break;
        }

        // Actualizar el contador de resultados en el log
        $this->actualizarContadorResultados($totalResultados);

        if ($request->wantsJson()) {
            return response()->json([
                'resultados' => $resultados,
                'total' => $totalResultados,
                'termino' => $termino,
                'tipo' => $tipo,
            ]);
        }

        return Inertia::render('Busqueda/Resultados', [
            'resultados' => $resultados,
            'total' => $totalResultados,
            'termino' => $termino,
            'tipo' => $tipo,
        ]);
    }

    /**
     * Búsqueda rápida para autocompletado en el header
     */
    public function busquedaRapida(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $termino = $validated['q'];
        $limit = $validated['limit'] ?? 5;

        $resultados = [];

        // Buscar cursos (siempre visible)
        $cursos = Curso::where('activo', true)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                    ->orWhere('nivel', 'ILIKE', "%{$termino}%");
            })
            ->select(['id', 'nombre', 'nivel', 'duracion_horas'])
            ->take($limit)
            ->get();

        $resultados['cursos'] = $cursos->map(function ($curso) {
            return [
                'id' => $curso->id,
                'titulo' => $curso->nombre,
                'subtitulo' => "Nivel: {$curso->nivel} - {$curso->duracion_horas}h",
                'tipo' => 'curso',
                'url' => route('publico.cursos.show', $curso->id),
                'icono' => 'academic-cap',
            ];
        });

        // Buscar certificados (público)
        $certificados = Certificado::where('codigo_verificacion', 'ILIKE', "%{$termino}%")
            ->with(['inscripcion.participante', 'inscripcion.curso'])
            ->take($limit)
            ->get();

        $resultados['certificados'] = $certificados->map(function ($certificado) {
            return [
                'id' => $certificado->id,
                'titulo' => "Certificado: {$certificado->inscripcion->curso->nombre}",
                'subtitulo' => "Código: {$certificado->codigo_verificacion}",
                'tipo' => 'certificado',
                'url' => route('publico.certificados.verificar', $certificado->codigo_verificacion),
                'icono' => 'certificate',
            ];
        });

        // Búsquedas adicionales solo para usuarios autenticados
        if (Auth::check()) {
            $user = Auth::user();

            // Participantes (para RESPONSABLE y ADMINISTRATIVO)
            if (in_array($user->rol, ['RESPONSABLE', 'ADMINISTRATIVO'])) {
                $participantes = Participante::where('activo', true)
                    ->where(function ($query) use ($termino) {
                        $query->where('nombre', 'ILIKE', "%{$termino}%")
                            ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                            ->orWhere('carnet', 'ILIKE', "%{$termino}%")
                            ->orWhere('email', 'ILIKE', "%{$termino}%");
                    })
                    ->take($limit)
                    ->get();

                $resultados['participantes'] = $participantes->map(function ($participante) {
                    return [
                        'id' => $participante->id,
                        'titulo' => $participante->nombre_completo,
                        'subtitulo' => "Carnet: {$participante->carnet}",
                        'tipo' => 'participante',
                        'url' => route('administrativo.participantes.show', $participante->id),
                        'icono' => 'user',
                    ];
                });
            }

            // Usuarios (solo para RESPONSABLE)
            if ($user->rol === 'RESPONSABLE') {
                $usuarios = Usuario::where('activo', true)
                    ->where(function ($query) use ($termino) {
                        $query->where('nombre', 'ILIKE', "%{$termino}%")
                            ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                            ->orWhere('carnet', 'ILIKE', "%{$termino}%")
                            ->orWhere('email', 'ILIKE', "%{$termino}%");
                    })
                    ->take($limit)
                    ->get();

                $resultados['usuarios'] = $usuarios->map(function ($usuario) {
                    return [
                        'id' => $usuario->id,
                        'titulo' => $usuario->nombre_completo,
                        'subtitulo' => "Rol: {$usuario->rol}",
                        'tipo' => 'usuario',
                        'url' => route('responsable.usuarios.show', $usuario->id),
                        'icono' => 'user-circle',
                    ];
                });
            }
        }

        // Registrar búsqueda rápida
        $totalResultados = collect($resultados)->flatten(1)->count();
        $this->registrarBusqueda($termino, $totalResultados);

        return response()->json([
            'resultados' => $resultados,
            'total' => $totalResultados,
            'termino' => $termino,
        ]);
    }

    /**
     * Obtener sugerencias de búsqueda
     */
    public function sugerencias(Request $request)
    {
        $user = Auth::user();

        // Términos más buscados (últimos 30 días)
        $terminosPopulares = BusquedaLog::select('termino', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->where('resultados', '>', 0)
            ->groupBy('termino')
            ->orderBy('total', 'desc')
            ->take(10)
            ->pluck('termino');

        // Cursos populares
        $cursosPopulares = Curso::withCount('inscripciones')
            ->where('activo', true)
            ->orderBy('inscripciones_count', 'desc')
            ->take(5)
            ->pluck('nombre');

        $sugerencias = [
            'terminos_populares' => $terminosPopulares,
            'cursos_populares' => $cursosPopulares,
        ];

        // Sugerencias específicas por rol
        if ($user) {
            switch ($user->rol) {
                case 'RESPONSABLE':
                    $sugerencias['acciones_rapidas'] = [
                        'Crear nuevo curso',
                        'Ver estadísticas',
                        'Gestionar usuarios',
                        'Emitir certificados',
                    ];
                    break;

                case 'ADMINISTRATIVO':
                    $sugerencias['acciones_rapidas'] = [
                        'Preinscripciones pendientes',
                        'Confirmar pagos',
                        'Ver inscripciones',
                        'Gestionar participantes',
                    ];
                    break;

                case 'TUTOR':
                    $sugerencias['acciones_rapidas'] = [
                        'Mis cursos',
                        'Calificar tareas',
                        'Ver asistencias',
                        'Crear nueva tarea',
                    ];
                    break;
            }
        }

        return response()->json($sugerencias);
    }

    /**
     * Estadísticas de búsquedas
     */
    public function estadisticas()
    {
        // Solo accesible para RESPONSABLE
        if (!Auth::check() || Auth::user()->rol !== 'RESPONSABLE') {
            abort(403, 'No autorizado');
        }

        $stats = [
            'total_busquedas' => BusquedaLog::count(),
            'busquedas_hoy' => BusquedaLog::whereDate('created_at', today())->count(),
            'busquedas_mes' => BusquedaLog::whereMonth('created_at', now()->month)->count(),
            'promedio_resultados' => round(BusquedaLog::avg('resultados'), 2),
            'busquedas_sin_resultados' => BusquedaLog::where('resultados', 0)->count(),
        ];

        $terminosMasBuscados = BusquedaLog::select('termino', DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('termino')
            ->orderBy('total', 'desc')
            ->take(20)
            ->get();

        $busquedasPorDia = BusquedaLog::selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'estadisticas' => $stats,
            'terminos_mas_buscados' => $terminosMasBuscados,
            'busquedas_por_dia' => $busquedasPorDia,
        ]);
    }

    /**
     * Buscar cursos
     */
    private function buscarCursos($termino)
    {
        return Curso::with(['tutor', 'gestion'])
            ->where('activo', true)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                    ->orWhere('nivel', 'ILIKE', "%{$termino}%")
                    ->orWhere('aula', 'ILIKE', "%{$termino}%");
            })
            ->orderBy('nombre')
            ->paginate(10);
    }

    /**
     * Buscar participantes
     */
    private function buscarParticipantes($termino)
    {
        return Participante::with(['tipoParticipante'])
            ->where('activo', true)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                    ->orWhere('carnet', 'ILIKE', "%{$termino}%")
                    ->orWhere('email', 'ILIKE', "%{$termino}%")
                    ->orWhere('universidad', 'ILIKE', "%{$termino}%");
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(10);
    }

    /**
     * Buscar usuarios
     */
    private function buscarUsuarios($termino)
    {
        return Usuario::where('activo', true)
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                    ->orWhere('carnet', 'ILIKE', "%{$termino}%")
                    ->orWhere('email', 'ILIKE', "%{$termino}%")
                    ->orWhere('rol', 'ILIKE', "%{$termino}%");
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(10);
    }

    /**
     * Buscar certificados
     */
    private function buscarCertificados($termino)
    {
        return Certificado::with(['inscripcion.participante', 'inscripcion.curso'])
            ->where(function ($query) use ($termino) {
                $query->where('codigo_verificacion', 'ILIKE', "%{$termino}%")
                    ->orWhere('tipo', 'ILIKE', "%{$termino}%")
                    ->orWhereHas('inscripcion.participante', function ($q) use ($termino) {
                        $q->where('nombre', 'ILIKE', "%{$termino}%")
                            ->orWhere('apellido', 'ILIKE', "%{$termino}%")
                            ->orWhere('carnet', 'ILIKE', "%{$termino}%");
                    })
                    ->orWhereHas('inscripcion.curso', function ($q) use ($termino) {
                        $q->where('nombre', 'ILIKE', "%{$termino}%");
                    });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Buscar en todo el sistema
     */
    private function buscarTodo($termino)
    {
        $resultados = [];

        // Cursos (siempre visible)
        $resultados['cursos'] = $this->buscarCursos($termino);

        // Certificados (siempre visible)
        $resultados['certificados'] = $this->buscarCertificados($termino);

        // Solo para usuarios autenticados
        if (Auth::check()) {
            $user = Auth::user();

            // Participantes (para RESPONSABLE y ADMINISTRATIVO)
            if (in_array($user->rol, ['RESPONSABLE', 'ADMINISTRATIVO'])) {
                $resultados['participantes'] = $this->buscarParticipantes($termino);
            }

            // Usuarios (solo para RESPONSABLE)
            if ($user->rol === 'RESPONSABLE') {
                $resultados['usuarios'] = $this->buscarUsuarios($termino);
            }
        }

        return $resultados;
    }

    /**
     * Registrar búsqueda en el log
     */
    private function registrarBusqueda($termino, $resultados = 0)
    {
        BusquedaLog::create([
            'termino' => $termino,
            'usuario_id' => Auth::id(),
            'resultados' => $resultados,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Actualizar contador de resultados en la última búsqueda
     */
    private function actualizarContadorResultados($total)
    {
        $ultimaBusqueda = BusquedaLog::where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($ultimaBusqueda) {
            $ultimaBusqueda->update(['resultados' => $total]);
        }
    }
}
