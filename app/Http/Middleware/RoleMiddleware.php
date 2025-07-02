<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar que el usuario esté activo
        if (!$user->activo) {
            Auth::logout();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Su cuenta ha sido desactivada.'], 403);
            }
            return redirect()->route('login')->with('error', 'Su cuenta ha sido desactivada. Contacte al administrador.');
        }

        // Verificar que el usuario tenga uno de los roles permitidos
        if (!in_array($user->rol, $roles)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'No tienes permisos para acceder a esta sección.'], 403);
            }
            // Redirigir según el rol actual del usuario
            return $this->redirectToUserDashboard($user->rol);
        }

        return $next($request);
    }

    /**
     * Redirigir al dashboard apropiado según el rol del usuario
     */
    private function redirectToUserDashboard(string $rol): Response
    {
        $message = 'No tienes permisos para acceder a esta sección.';

        switch ($rol) {
            case 'RESPONSABLE':
                return redirect()->route('dashboard.responsable')->with('warning', $message);
            case 'ADMINISTRATIVO':
                return redirect()->route('dashboard.administrativo')->with('warning', $message);
            case 'TUTOR':
                return redirect()->route('dashboard.tutor')->with('warning', $message);
            default:
                return redirect()->route('dashboard')->with('error', $message);
        }
    }
}
