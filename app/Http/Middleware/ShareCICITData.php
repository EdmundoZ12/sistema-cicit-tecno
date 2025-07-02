<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\ConfiguracionSitio;
use App\Models\MenuItem;
use App\Models\VisitaPagina;
use Illuminate\Support\Facades\DB;

class ShareCICITData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Compartir datos globales de CICIT con todas las vistas de Inertia
        Inertia::share([
            'siteConfig' => $this->getSiteConfig(),
            'siteStats' => $this->getSiteStats(),
            'menuItems' => $this->getMenuItems(),
            'notifications' => $this->getNotifications(),
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                    'error' => $request->session()->get('error'),
                    'warning' => $request->session()->get('warning'),
                    'info' => $request->session()->get('info'),
                ];
            },
        ]);

        return $next($request);
    }

    /**
     * Obtener configuración del sitio
     */
    private function getSiteConfig()
    {
        return cache()->remember('cicit.site_config', 60 * 60, function () {
            $config = ConfiguracionSitio::first();

            if (!$config) {
                return [
                    'nombre_sitio' => 'CICIT - UAGRM',
                    'logo_url' => '/images/cicit-logo.png',
                    'descripcion_sitio' => 'Centro Integral de Certificación e Innovación Tecnológica',
                    'keywords_sitio' => 'certificación, tecnología, cursos, UAGRM',
                    'meta_description' => 'Plataforma de certificación y capacitación tecnológica de la UAGRM',
                    'color_primario' => '#1e40af',
                    'color_secundario' => '#3b82f6',
                    'contador_visitas_activo' => true,
                    'mantenimiento_activo' => false,
                    'telefono_contacto' => '+591 3 336-4000',
                    'email_contacto' => 'cicit@uagrm.edu.bo',
                    'direccion_fisica' => 'Universidad Autónoma Gabriel René Moreno, Santa Cruz, Bolivia',
                    'redes_sociales' => [
                        'facebook' => 'https://facebook.com/uagrm',
                        'twitter' => 'https://twitter.com/uagrm',
                        'instagram' => 'https://instagram.com/uagrm',
                        'linkedin' => 'https://linkedin.com/company/uagrm'
                    ]
                ];
            }

            // Decodificar redes sociales si es string JSON
            if (isset($config->redes_sociales) && is_string($config->redes_sociales)) {
                $config->redes_sociales = json_decode($config->redes_sociales, true);
            }

            return $config->toArray();
        });
    }

    /**
     * Obtener estadísticas del sitio
     */
    private function getSiteStats()
    {
        return cache()->remember('cicit.site_stats', 5 * 60, function () {
            return [
                'total_visits' => VisitaPagina::count(),
                'unique_visitors' => VisitaPagina::distinct('ip_address')->count('ip_address'),
                'total_users' => DB::table('USUARIO')->count(),
                'total_courses' => DB::table('CURSO')->count(),
                'total_registrations' => DB::table('INSCRIPCION')->count(),
                'active_courses' => DB::table('CURSO')->where('activo', true)->count(),
                'visits_today' => VisitaPagina::whereDate('fecha_visita', today())->count(),
                'visits_this_week' => VisitaPagina::whereBetween('fecha_visita', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
            ];
        });
    }

    /**
     * Obtener menú dinámico según el usuario
     */
    private function getMenuItems()
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $userRole = $user->tipo_participante ?? 'PARTICIPANTE';

        return cache()->remember("cicit.menu_items.{$userRole}", 30 * 60, function () use ($userRole) {
            return MenuItem::where('activo', true)
                ->where(function($query) use ($userRole) {
                    $query->where('rol', $userRole)->orWhere('rol', 'TODOS');
                })
                ->orderBy('orden')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'titulo' => $item->titulo,
                        'icono' => $item->icono,
                        'ruta' => $item->ruta,
                        'orden' => $item->orden,
                        'activo' => $item->activo,
                        'padre_id' => $item->padre_id,
                        'rol' => $item->rol,
                    ];
                });
        });
    }

    /**
     * Obtener notificaciones del usuario
     */
    private function getNotifications()
    {
        if (!Auth::check()) {
            return [];
        }

        // Por ahora retornamos array vacío, se puede implementar después
        return [];
    }
}
