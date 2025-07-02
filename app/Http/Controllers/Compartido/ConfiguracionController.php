<?php

namespace App\Http\Controllers\Compartido;

use App\Http\Controllers\Controller;
use App\Models\TemaConfiguracion;
use App\Models\ConfiguracionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ConfiguracionController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Las rutas API ya están protegidas por middleware en web.php
    }

    /**
     * Mostrar configuración de temas y accesibilidad
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Obtener configuración actual del usuario
        $configuracionUsuario = ConfiguracionUsuario::where('usuario_id', $user->id)->first();

        // Obtener todos los temas disponibles
        $temasDisponibles = TemaConfiguracion::where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Detectar hora del sistema para modo automático
        $horaActual = now()->hour;
        $esModoNocturno = $horaActual < 6 || $horaActual >= 18; // 6 PM a 6 AM

        return Inertia::render('Configuracion/TemasAccesibilidad', [
            'configuracion_actual' => $configuracionUsuario,
            'temas_disponibles' => $temasDisponibles,
            'es_modo_nocturno' => $esModoNocturno,
            'hora_actual' => $horaActual,
            'tamanos_fuente' => $this->getTamanosFuente(),
        ]);
    }

    /**
     * Actualizar configuración de tema del usuario
     */
    public function actualizarTema(Request $request)
    {
        $validated = $request->validate([
            'tema_id' => ['nullable', 'exists:TEMA_CONFIGURACION,id'],
            'tamano_fuente' => ['nullable', 'integer', 'min:12', 'max:24'],
            'alto_contraste' => ['nullable', 'boolean'],
            'modo_automatico' => ['nullable', 'boolean'],
        ], [
            'tema_id.exists' => 'El tema seleccionado no es válido.',
            'tamano_fuente.min' => 'El tamaño de fuente mínimo es 12px.',
            'tamano_fuente.max' => 'El tamaño de fuente máximo es 24px.',
        ]);

        $user = Auth::user();

        // Crear o actualizar configuración del usuario
        $configuracion = ConfiguracionUsuario::updateOrCreate(
            ['usuario_id' => $user->id],
            [
                'tema_id' => $validated['tema_id'] ?? null,
                'tamano_fuente' => $validated['tamano_fuente'] ?? 16,
                'alto_contraste' => $validated['alto_contraste'] ?? false,
                'modo_automatico' => $validated['modo_automatico'] ?? true,
            ]
        );

        // Si tiene modo automático activado, determinar tema según la hora
        if ($configuracion->modo_automatico) {
            $horaActual = now()->hour;
            $esModoNocturno = $horaActual < 6 || $horaActual >= 18;

            if ($esModoNocturno) {
                $temaNocturno = TemaConfiguracion::where('modo_oscuro', true)
                    ->where('activo', true)
                    ->first();

                if ($temaNocturno) {
                    $configuracion->update(['tema_id' => $temaNocturno->id]);
                }
            } else {
                $temaDiurno = TemaConfiguracion::where('modo_oscuro', false)
                    ->where('activo', true)
                    ->first();

                if ($temaDiurno && $validated['tema_id']) {
                    $configuracion->update(['tema_id' => $validated['tema_id']]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Configuración de tema actualizada exitosamente.',
            'configuracion' => $configuracion->fresh(['tema']),
        ]);
    }

    /**
     * Obtener tema actual del usuario
     */
    public function temaActual()
    {
        $user = Auth::user();

        if (!$user) {
            // Usuario no autenticado, usar tema por defecto
            $temaDefecto = TemaConfiguracion::where('activo', true)
                ->where('target_edad', 'adultos')
                ->where('modo_oscuro', false)
                ->first();

            return response()->json([
                'tema' => $temaDefecto,
                'configuracion' => [
                    'tamano_fuente' => 16,
                    'alto_contraste' => false,
                    'modo_automatico' => true,
                ],
            ]);
        }

        $configuracion = ConfiguracionUsuario::with('tema')
            ->where('usuario_id', $user->id)
            ->first();

        if (!$configuracion) {
            // Crear configuración por defecto
            $temaDefecto = TemaConfiguracion::where('activo', true)
                ->where('target_edad', 'adultos')
                ->where('modo_oscuro', false)
                ->first();

            $configuracion = ConfiguracionUsuario::create([
                'usuario_id' => $user->id,
                'tema_id' => $temaDefecto->id ?? null,
                'tamano_fuente' => 16,
                'alto_contraste' => false,
                'modo_automatico' => true,
            ]);

            $configuracion->load('tema');
        }

        // Verificar modo automático
        if ($configuracion->modo_automatico) {
            $horaActual = now()->hour;
            $esModoNocturno = $horaActual < 6 || $horaActual >= 18;

            if ($esModoNocturno) {
                $temaNocturno = TemaConfiguracion::where('modo_oscuro', true)
                    ->where('activo', true)
                    ->first();

                if ($temaNocturno && $configuracion->tema_id !== $temaNocturno->id) {
                    $configuracion->update(['tema_id' => $temaNocturno->id]);
                    $configuracion->load('tema');
                }
            }
        }

        return response()->json([
            'tema' => $configuracion->tema,
            'configuracion' => [
                'tamano_fuente' => $configuracion->tamano_fuente,
                'alto_contraste' => $configuracion->alto_contraste,
                'modo_automatico' => $configuracion->modo_automatico,
            ],
        ]);
    }

    /**
     * Obtener CSS dinámico según la configuración del usuario
     */
    public function cssPersonalizado()
    {
        $user = Auth::user();

        $configuracion = null;
        if ($user) {
            $configuracion = ConfiguracionUsuario::with('tema')
                ->where('usuario_id', $user->id)
                ->first();
        }

        // Configuración por defecto si no hay usuario o configuración
        if (!$configuracion) {
            $temaDefecto = TemaConfiguracion::where('activo', true)
                ->where('target_edad', 'adultos')
                ->where('modo_oscuro', false)
                ->first();

            $css = $this->generarCSSTema($temaDefecto, 16, false);
        } else {
            $css = $this->generarCSSTema(
                $configuracion->tema,
                $configuracion->tamano_fuente,
                $configuracion->alto_contraste
            );
        }

        return response($css)
            ->header('Content-Type', 'text/css')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache por 1 hora
    }

    /**
     * Cambiar tamaño de fuente rápidamente
     */
    public function cambiarTamanoFuente(Request $request)
    {
        $validated = $request->validate([
            'accion' => ['required', 'string', 'in:aumentar,disminuir,reset'],
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $configuracion = ConfiguracionUsuario::where('usuario_id', $user->id)->first();

        if (!$configuracion) {
            // Crear configuración por defecto
            $configuracion = ConfiguracionUsuario::create([
                'usuario_id' => $user->id,
                'tamano_fuente' => 16,
                'alto_contraste' => false,
                'modo_automatico' => true,
            ]);
        }

        $tamanoActual = $configuracion->tamano_fuente;

        switch ($validated['accion']) {
            case 'aumentar':
                $nuevoTamano = min($tamanoActual + 2, 24);
                break;
            case 'disminuir':
                $nuevoTamano = max($tamanoActual - 2, 12);
                break;
            case 'reset':
                $nuevoTamano = 16;
                break;
            default:
                $nuevoTamano = $tamanoActual;
        }

        $configuracion->update(['tamano_fuente' => $nuevoTamano]);

        return response()->json([
            'success' => true,
            'tamano_anterior' => $tamanoActual,
            'tamano_nuevo' => $nuevoTamano,
            'mensaje' => "Tamaño de fuente cambiado a {$nuevoTamano}px",
        ]);
    }

    /**
     * Alternar alto contraste
     */
    public function alternarAltoContraste()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $configuracion = ConfiguracionUsuario::where('usuario_id', $user->id)->first();

        if (!$configuracion) {
            $configuracion = ConfiguracionUsuario::create([
                'usuario_id' => $user->id,
                'tamano_fuente' => 16,
                'alto_contraste' => true, // Activar alto contraste
                'modo_automatico' => true,
            ]);
        } else {
            $configuracion->update([
                'alto_contraste' => !$configuracion->alto_contraste
            ]);
        }

        return response()->json([
            'success' => true,
            'alto_contraste' => $configuracion->alto_contraste,
            'mensaje' => $configuracion->alto_contraste
                ? 'Alto contraste activado'
                : 'Alto contraste desactivado',
        ]);
    }

    /**
     * Obtener tamaños de fuente disponibles
     */
    private function getTamanosFuente()
    {
        return [
            ['valor' => 12, 'nombre' => 'Muy Pequeña (12px)'],
            ['valor' => 14, 'nombre' => 'Pequeña (14px)'],
            ['valor' => 16, 'nombre' => 'Normal (16px)'],
            ['valor' => 18, 'nombre' => 'Grande (18px)'],
            ['valor' => 20, 'nombre' => 'Muy Grande (20px)'],
            ['valor' => 22, 'nombre' => 'Extra Grande (22px)'],
            ['valor' => 24, 'nombre' => 'Máxima (24px)'],
        ];
    }

    /**
     * Generar CSS personalizado según el tema y configuraciones
     */
    private function generarCSSTema($tema, $tamanoFuente, $altoContraste)
    {
        if (!$tema) {
            return '/* Tema no encontrado */';
        }

        $css = ":root {\n";
        $css .= "  --color-primario: {$tema->color_primario};\n";
        $css .= "  --color-secundario: {$tema->color_secundario};\n";
        $css .= "  --color-fondo: {$tema->color_fondo};\n";
        $css .= "  --color-texto: {$tema->color_texto};\n";
        $css .= "  --tamano-fuente-base: {$tamanoFuente}px;\n";

        if ($altoContraste) {
            // Colores de alto contraste
            $css .= "  --color-texto: #000000;\n";
            $css .= "  --color-fondo: #FFFFFF;\n";
            $css .= "  --color-primario: #0000FF;\n";
            $css .= "  --color-secundario: #FF0000;\n";
        }

        $css .= "}\n\n";

        // Aplicar estilos base
        $css .= "body {\n";
        $css .= "  font-size: var(--tamano-fuente-base);\n";
        $css .= "  background-color: var(--color-fondo);\n";
        $css .= "  color: var(--color-texto);\n";
        $css .= "}\n\n";

        // Modo oscuro
        if ($tema->modo_oscuro) {
            $css .= "html.dark {\n";
            $css .= "  --color-fondo: #1a1a1a;\n";
            $css .= "  --color-texto: #ffffff;\n";
            $css .= "}\n\n";
        }

        // Estilos de accesibilidad
        if ($altoContraste) {
            $css .= ".alto-contraste {\n";
            $css .= "  filter: contrast(150%);\n";
            $css .= "}\n\n";

            $css .= "a {\n";
            $css .= "  text-decoration: underline !important;\n";
            $css .= "  font-weight: bold !important;\n";
            $css .= "}\n\n";

            $css .= "button {\n";
            $css .= "  border: 2px solid var(--color-texto) !important;\n";
            $css .= "}\n\n";
        }

        // Tamaños de fuente responsivos
        $css .= "@media (max-width: 768px) {\n";
        $css .= "  body {\n";
        $css .= "    font-size: calc(var(--tamano-fuente-base) * 0.9);\n";
        $css .= "  }\n";
        $css .= "}\n\n";

        return $css;
    }

    /**
     * API: Obtener temas disponibles
     */
    public function temasDisponibles()
    {
        $temas = TemaConfiguracion::where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($tema) {
                return [
                    'id' => $tema->id,
                    'nombre' => $tema->nombre,
                    'descripcion' => $tema->descripcion,
                    'color_primario' => $tema->color_primario,
                    'color_secundario' => $tema->color_secundario,
                    'color_fondo' => $tema->color_fondo,
                    'color_texto' => $tema->color_texto,
                    'tamano_fuente_base' => $tema->tamano_fuente_base,
                    'alto_contraste' => $tema->alto_contraste,
                    'target_edad' => $tema->target_edad,
                    'modo_oscuro' => $tema->modo_oscuro
                ];
            });

        return response()->json(['themes' => $temas]);
    }

    /**
     * API: Obtener configuración del usuario actual
     */
    public function configuracionUsuario()
    {
        $user = Auth::user();

        $configuracion = ConfiguracionUsuario::with('tema')
            ->where('usuario_id', $user->id)
            ->first();

        if (!$configuracion) {
            // Crear configuración por defecto
            $temaDefecto = TemaConfiguracion::where('activo', true)
                ->where('target_edad', 'adultos')
                ->where('modo_oscuro', false)
                ->first();

            $configuracion = ConfiguracionUsuario::create([
                'usuario_id' => $user->id,
                'tema_id' => $temaDefecto?->id ?? 1,
                'tamano_fuente' => 16,
                'alto_contraste' => false,
                'modo_automatico' => false,
            ]);

            $configuracion->load('tema');
        }

        return response()->json([
            'tema_id' => $configuracion->tema_id,
            'tamano_fuente' => $configuracion->tamano_fuente,
            'alto_contraste' => $configuracion->alto_contraste,
            'modo_automatico' => $configuracion->modo_automatico,
        ]);
    }

    /**
     * API: Guardar configuración del usuario
     */
    public function guardarConfiguracionUsuario(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'tema_id' => 'nullable|integer|exists:TEMA_CONFIGURACION,id',
            'tamano_fuente' => 'nullable|integer|min:12|max:24',
            'alto_contraste' => 'nullable|boolean',
            'modo_automatico' => 'nullable|boolean'
        ]);

        $configuracion = ConfiguracionUsuario::updateOrCreate(
            ['usuario_id' => $user->id],
            [
                'tema_id' => $request->tema_id ?? 1,
                'tamano_fuente' => $request->tamano_fuente ?? 16,
                'alto_contraste' => $request->alto_contraste ?? false,
                'modo_automatico' => $request->modo_automatico ?? false
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada correctamente',
            'configuracion' => $configuracion
        ]);
    }
}
