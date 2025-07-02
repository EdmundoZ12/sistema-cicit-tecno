<?php

namespace App\Http\Middleware;

use App\Models\VisitaPagina;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ContadorVisitas
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar visitas para respuestas exitosas y métodos GET
        if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
            $this->registrarVisita($request);
        }

        return $response;
    }

    /**
     * Registrar la visita en la base de datos
     */
    private function registrarVisita(Request $request): void
    {
        try {
            // Obtener la página visitada
            $pagina = $request->getPathInfo();

            // Excluir ciertas rutas que no necesitan ser contadas
            $rutasExcluidas = [
                '/api/',
                '/livewire/',
                '/_ignition/',
                '/css/',
                '/js/',
                '/images/',
                '/favicon.',
                '/robots.txt',
                '/sitemap.',
            ];

            foreach ($rutasExcluidas as $ruta) {
                if (str_starts_with($pagina, $ruta)) {
                    return;
                }
            }

            // Evitar registrar la misma visita múltiples veces en poco tiempo
            // (mismo usuario, misma página, menos de 30 minutos)
            $visitaReciente = VisitaPagina::where('pagina', $pagina)
                ->where('session_id', session()->getId())
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();

            if ($visitaReciente) {
                return;
            }

            // Registrar la visita
            VisitaPagina::create([
                'pagina' => $pagina,
                'ip_address' => $request->ip(),
                'usuario_id' => Auth::check() ? Auth::id() : null,
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
            ]);

        } catch (\Exception $e) {
            // No queremos que fallos en el contador rompan la aplicación
            // Loguear el error pero continuar
            logger()->warning('Error al registrar visita: ' . $e->getMessage());
        }
    }
}
