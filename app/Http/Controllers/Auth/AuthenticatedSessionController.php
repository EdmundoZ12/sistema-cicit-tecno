<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => false, // No mostramos opción de reset de password
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Cargar la configuración del usuario autenticado para aplicar su tema
        $user = Auth::user();
        if ($user && $user->configuracion) {
            // Guardar la configuración del tema en la sesión
            session([
                'user_theme_config' => [
                    'tema_id' => $user->configuracion->tema_id,
                    'tamano_fuente' => $user->configuracion->tamano_fuente,
                    'alto_contraste' => $user->configuracion->alto_contraste,
                    'modo_automatico' => $user->configuracion->modo_automatico,
                ]
            ]);
        }

        // Redirigir al dashboard general que maneja la redirección según el rol
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
