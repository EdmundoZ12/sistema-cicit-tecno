<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\ConfiguracionSitio;
use App\Models\TipoParticipante;
use App\Models\Certificado;
use App\Models\VisitaPagina;
use App\Models\Estadistica;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class InicioController extends Controller
{
    /**
     * Página principal del CICIT
     */
    public function index(Request $request): Response
    {
        // Registrar visita a la página principal
        VisitaPagina::registrarVisita('/', $request);

        // Obtener configuración del sitio
        $configuracionSitio = ConfiguracionSitio::getConfiguracionCompleta();

        // Obtener cursos destacados (activos y próximos)
        $cursosDestacados = Curso::activo()
            ->with(['tutor', 'gestion', 'precios.tipoParticipante'])
            ->where('fecha_inicio', '>=', Carbon::now())
            ->orderBy('fecha_inicio')
            ->take(6)
            ->get()
            ->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'descripcion' => $curso->descripcion,
                    'duracion_horas' => $curso->duracion_horas,
                    'nivel' => $curso->nivel,
                    'logo_url' => $curso->logo_url,
                    'tutor' => $curso->tutor->nombre_completo,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'fecha_fin' => $curso->fecha_fin->format('d/m/Y'),
                    'cupos_disponibles' => $curso->cupos_disponibles,
                    'aula' => $curso->aula,
                    'precios' => $curso->precios->map(function ($precio) {
                        return [
                            'tipo' => $precio->tipoParticipante->codigo,
                            'descripcion' => $precio->tipoParticipante->descripcion,
                            'precio' => $precio->precio_formateado,
                        ];
                    }),
                ];
            });

        // Estadísticas públicas del CICIT
        $estadisticasPublicas = [
            'total_cursos_ofrecidos' => Curso::count(),
            'estudiantes_certificados' => Certificado::count(),
            'cursos_activos' => Curso::activo()->count(),
            'areas_certificacion' => $this->getAreasCertificacion(),
        ];

        // Información institucional
        $informacionInstitucional = [
            'mision' => $configuracionSitio['sitio']['mision'],
            'vision' => $configuracionSitio['sitio']['vision'],
            'areas_certificacion' => ConfiguracionSitio::obtener('areas_certificacion', ''),
            'modalidades' => ConfiguracionSitio::obtener('modalidades', ''),
        ];

        // Tipos de participante para mostrar precios
        $tiposParticipante = TipoParticipante::activo()
            ->get(['id', 'codigo', 'descripcion'])
            ->map(function ($tipo) {
                return [
                    'codigo' => $tipo->codigo,
                    'descripcion' => $tipo->descripcion,
                ];
            });

        return Inertia::render('Publico/Inicio', [
            'configuracionSitio' => $configuracionSitio,
            'cursosDestacados' => $cursosDestacados,
            'estadisticasPublicas' => $estadisticasPublicas,
            'informacionInstitucional' => $informacionInstitucional,
            'tiposParticipante' => $tiposParticipante,
            'contadorVisitas' => VisitaPagina::getTextoContador('/'),
        ]);
    }

    /**
     * Página "Acerca de CICIT"
     */
    public function acerca(Request $request): Response
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/acerca', $request);

        $configuracion = ConfiguracionSitio::getConfiguracionCompleta();

        $informacionDetallada = [
            'nombre_completo' => ConfiguracionSitio::obtener('nombre_sitio'),
            'descripcion' => ConfiguracionSitio::obtener('descripcion_sitio'),
            'mision' => ConfiguracionSitio::obtener('mision'),
            'vision' => ConfiguracionSitio::obtener('vision'),
            'institucion_matriz' => ConfiguracionSitio::obtener('institucion_matriz'),
            'facultad' => ConfiguracionSitio::obtener('facultad'),
            'areas_certificacion' => ConfiguracionSitio::obtener('areas_certificacion'),
            'modalidades' => ConfiguracionSitio::obtener('modalidades'),
            'desarrollado_por' => ConfiguracionSitio::obtener('desarrollado_por'),
            'version_sistema' => ConfiguracionSitio::obtener('version_sistema'),
        ];

        // Estadísticas para mostrar logros
        $logros = [
            'cursos_completados' => Curso::where('fecha_fin', '<', Carbon::now())->count(),
            'estudiantes_graduados' => Certificado::aprobacion()->count() + Certificado::mencionHonor()->count(),
            'horas_formacion' => Curso::sum('duracion_horas'),
            'areas_especializacion' => $this->contarAreasEspecializacion(),
        ];

        return Inertia::render('Publico/Acerca', [
            'configuracion' => $configuracion,
            'informacion' => $informacionDetallada,
            'logros' => $logros,
            'contadorVisitas' => VisitaPagina::getTextoContador('/acerca'),
        ]);
    }

    /**
     * Página de contacto
     */
    public function contacto(Request $request): Response
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/contacto', $request);

        $informacionContacto = ConfiguracionSitio::getInformacionContacto();

        $redesSociales = ConfiguracionSitio::obtener('redes_sociales', []);
        if (is_string($redesSociales)) {
            $redesSociales = json_decode($redesSociales, true) ?? [];
        }

        return Inertia::render('Publico/Contacto', [
            'informacion' => $informacionContacto,
            'redes_sociales' => $redesSociales,
            'horario_atencion' => ConfiguracionSitio::obtener('horario_atencion'),
            'contadorVisitas' => VisitaPagina::getTextoContador('/contacto'),
        ]);
    }

    /**
     * Búsqueda general en el sitio público
     */
    public function buscar(Request $request)
    {
        $termino = $request->input('q', '');

        if (empty($termino)) {
            return response()->json([
                'termino' => '',
                'total_resultados' => 0,
                'resultados' => [],
            ]);
        }

        // Buscar solo en contenido público
        $resultados = [];
        $totalResultados = 0;

        // Buscar en cursos activos y próximos
        $cursos = Curso::activo()
            ->where(function ($query) use ($termino) {
                $query->where('nombre', 'ILIKE', "%{$termino}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                    ->orWhere('nivel', 'ILIKE', "%{$termino}%");
            })
            ->with(['tutor', 'precios.tipoParticipante'])
            ->take(10)
            ->get();

        if ($cursos->count() > 0) {
            $resultados['cursos'] = $cursos->map(function ($curso) {
                return [
                    'id' => $curso->id,
                    'nombre' => $curso->nombre,
                    'descripcion' => substr($curso->descripcion, 0, 150) . '...',
                    'tutor' => $curso->tutor->nombre_completo,
                    'fecha_inicio' => $curso->fecha_inicio->format('d/m/Y'),
                    'url' => "/cursos/{$curso->id}",
                ];
            });
            $totalResultados += $cursos->count();
        }

        // Buscar en configuración del sitio (páginas estáticas)
        $paginasEstaticas = [
            ['titulo' => 'Inicio', 'url' => '/', 'descripcion' => 'Página principal del CICIT'],
            ['titulo' => 'Acerca de CICIT', 'url' => '/acerca', 'descripcion' => 'Información sobre el centro'],
            ['titulo' => 'Contacto', 'url' => '/contacto', 'descripcion' => 'Información de contacto'],
            ['titulo' => 'Cursos', 'url' => '/cursos', 'descripcion' => 'Catálogo de cursos disponibles'],
            ['titulo' => 'Verificar Certificado', 'url' => '/verificar-certificado', 'descripcion' => 'Verificación de certificados'],
        ];

        $paginasEncontradas = array_filter($paginasEstaticas, function ($pagina) use ($termino) {
            return stripos($pagina['titulo'], $termino) !== false ||
                stripos($pagina['descripcion'], $termino) !== false;
        });

        if (!empty($paginasEncontradas)) {
            $resultados['paginas'] = array_values($paginasEncontradas);
            $totalResultados += count($paginasEncontradas);
        }

        // Registrar búsqueda para analytics
        \App\Models\BusquedaLog::registrarBusqueda($termino, $totalResultados, $request);

        return response()->json([
            'termino' => $termino,
            'total_resultados' => $totalResultados,
            'resultados' => $resultados,
        ]);
    }

    /**
     * API para obtener información básica del sitio
     */
    public function informacionBasica()
    {
        return response()->json([
            'nombre_sitio' => ConfiguracionSitio::obtener('nombre_corto', 'CICIT'),
            'descripcion' => ConfiguracionSitio::obtener('descripcion_sitio'),
            'logo' => ConfiguracionSitio::obtener('logo_principal'),
            'contacto' => ConfiguracionSitio::getInformacionContacto(),
            'modo_mantenimiento' => ConfiguracionSitio::estaEnMantenimiento(),
        ]);
    }

    /**
     * Estadísticas públicas para widgets
     */
    public function estadisticasPublicas()
    {
        $stats = [
            'cursos_disponibles' => Curso::activo()
                ->where('fecha_inicio', '>=', Carbon::now())
                ->count(),
            'total_estudiantes_certificados' => Certificado::count(),
            'areas_certificacion' => $this->getAreasCertificacion(),
            'modalidades_disponibles' => ['Presencial', 'Semipresencial', 'Virtual'],
        ];

        return response()->json($stats);
    }

    /**
     * Página de mantenimiento
     */
    public function mantenimiento(): Response
    {
        return Inertia::render('Publico/Mantenimiento', [
            'mensaje' => 'El sistema CICIT está en mantenimiento. Volveremos pronto.',
            'contacto' => ConfiguracionSitio::obtener('email_contacto'),
        ]);
    }

    /**
     * Obtener áreas de certificación desde la configuración
     */
    private function getAreasCertificacion()
    {
        $areas = ConfiguracionSitio::obtener('areas_certificacion', '');

        if (empty($areas)) {
            return ['Desarrollo de Software', 'Ciberseguridad', 'Inteligencia Artificial'];
        }

        return explode(', ', $areas);
    }

    /**
     * Contar áreas de especialización basado en cursos
     */
    private function contarAreasEspecializacion()
    {
        // Contar diferentes niveles de cursos como proxy para áreas
        return Curso::distinct('nivel')->count('nivel');
    }

    /**
     * Middleware para verificar modo mantenimiento
     */
    public function verificarMantenimiento(Request $request, $next)
    {
        if (ConfiguracionSitio::estaEnMantenimiento()) {
            return redirect()->route('publico.mantenimiento');
        }

        return $next($request);
    }

    /**
     * Obtener últimas noticias/actualizaciones (placeholder)
     */
    public function noticias()
    {
        // Placeholder para futuras noticias del CICIT
        $noticias = [
            [
                'titulo' => 'Nuevos cursos de Inteligencia Artificial disponibles',
                'fecha' => Carbon::now()->subDays(5)->format('d/m/Y'),
                'resumen' => 'Inscripciones abiertas para cursos especializados en IA.',
            ],
            [
                'titulo' => 'Certificación en Ciberseguridad - Inscripciones abiertas',
                'fecha' => Carbon::now()->subDays(10)->format('d/m/Y'),
                'resumen' => 'Nuevo programa de certificación en ciberseguridad.',
            ],
        ];

        return response()->json($noticias);
    }
}
