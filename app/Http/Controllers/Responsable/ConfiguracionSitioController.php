<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSitio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ConfiguracionSitioController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede gestionar configuración del sitio
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
        $configuraciones = ConfiguracionSitio::query()
            ->when($request->search, function ($query, $search) {
                $query->where('clave', 'ILIKE', "%{$search}%")
                    ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                    ->orWhere('valor', 'ILIKE', "%{$search}%");
            })
            ->when($request->tipo, function ($query, $tipo) {
                $query->where('tipo', $tipo);
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->orderBy('clave')
            ->paginate(20)
            ->withQueryString();

        // Agrupar configuraciones por categorías
        $configuracionesAgrupadas = $this->agruparConfiguraciones();

        return Inertia::render('Responsable/ConfiguracionSitio/Index', [
            'configuraciones' => $configuraciones,
            'configuracionesAgrupadas' => $configuracionesAgrupadas,
            'filters' => $request->only(['search', 'tipo', 'activo']),
            'tipos' => ['string', 'number', 'boolean', 'json'],
            'estadisticas' => [
                'total' => ConfiguracionSitio::count(),
                'activas' => ConfiguracionSitio::where('activo', true)->count(),
                'por_tipo' => ConfiguracionSitio::groupBy('tipo')
                    ->selectRaw('tipo, count(*) as total')
                    ->pluck('total', 'tipo'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Responsable/ConfiguracionSitio/Create', [
            'tipos' => [
                'string' => 'Texto',
                'number' => 'Número',
                'boolean' => 'Verdadero/Falso',
                'json' => 'JSON (Datos complejos)'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => ['required', 'string', 'max:100', 'unique:CONFIGURACION_SITIO,clave'],
            'valor' => ['required'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['string', 'number', 'boolean', 'json'])],
        ], [
            'clave.required' => 'La clave es obligatoria.',
            'clave.unique' => 'Ya existe una configuración con esta clave.',
            'clave.max' => 'La clave no puede tener más de 100 caracteres.',
            'valor.required' => 'El valor es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo seleccionado no es válido.',
        ]);

        // Validar valor según el tipo
        $valorValidado = $this->validarValorPorTipo($validated['valor'], $validated['tipo']);

        if ($valorValidado === false) {
            return back()->withErrors([
                'valor' => $this->getMensajeErrorTipo($validated['tipo'])
            ])->withInput();
        }

        ConfiguracionSitio::create([
            'clave' => $validated['clave'],
            'valor' => $valorValidado,
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'activo' => true,
        ]);

        // Limpiar caché de configuraciones
        Cache::forget('config_sitio');

        return redirect()->route('responsable.configuracion-sitio.index')
            ->with('success', 'Configuración creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConfiguracionSitio $configuracionSitio): Response
    {
        return Inertia::render('Responsable/ConfiguracionSitio/Show', [
            'configuracion' => [
                'id' => $configuracionSitio->id,
                'clave' => $configuracionSitio->clave,
                'valor' => $configuracionSitio->valor,
                'valor_casteado' => $configuracionSitio->valor_casteado,
                'descripcion' => $configuracionSitio->descripcion,
                'tipo' => $configuracionSitio->tipo,
                'activo' => $configuracionSitio->activo,
                'created_at' => $configuracionSitio->created_at,
                'updated_at' => $configuracionSitio->updated_at,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConfiguracionSitio $configuracionSitio): Response
    {
        return Inertia::render('Responsable/ConfiguracionSitio/Edit', [
            'configuracion' => $configuracionSitio,
            'tipos' => [
                'string' => 'Texto',
                'number' => 'Número',
                'boolean' => 'Verdadero/Falso',
                'json' => 'JSON (Datos complejos)'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConfiguracionSitio $configuracionSitio)
    {
        $validated = $request->validate([
            'clave' => [
                'required',
                'string',
                'max:100',
                Rule::unique('CONFIGURACION_SITIO', 'clave')->ignore($configuracionSitio->id)
            ],
            'valor' => ['required'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['string', 'number', 'boolean', 'json'])],
            'activo' => ['boolean'],
        ], [
            'clave.unique' => 'Ya existe otra configuración con esta clave.',
        ]);

        // Validar valor según el tipo
        $valorValidado = $this->validarValorPorTipo($validated['valor'], $validated['tipo']);

        if ($valorValidado === false) {
            return back()->withErrors([
                'valor' => $this->getMensajeErrorTipo($validated['tipo'])
            ])->withInput();
        }

        $configuracionSitio->update([
            'clave' => $validated['clave'],
            'valor' => $valorValidado,
            'descripcion' => $validated['descripcion'],
            'tipo' => $validated['tipo'],
            'activo' => $validated['activo'] ?? $configuracionSitio->activo,
        ]);

        // Limpiar caché de configuraciones
        Cache::forget('config_sitio');

        return redirect()->route('responsable.configuracion-sitio.index')
            ->with('success', 'Configuración actualizada exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo(ConfiguracionSitio $configuracionSitio)
    {
        $configuracionSitio->update(['activo' => !$configuracionSitio->activo]);

        Cache::forget('config_sitio');

        $estado = $configuracionSitio->activo ? 'activada' : 'desactivada';

        return back()->with('success', "Configuración {$estado} exitosamente.");
    }

    /**
     * Actualizar múltiples configuraciones a la vez
     */
    public function actualizarMasivo(Request $request)
    {
        $validated = $request->validate([
            'configuraciones' => ['required', 'array'],
            'configuraciones.*.id' => ['required', 'exists:CONFIGURACION_SITIO,id'],
            'configuraciones.*.valor' => ['required'],
        ], [
            'configuraciones.required' => 'Debe proporcionar configuraciones para actualizar.',
            'configuraciones.*.valor.required' => 'Todos los valores son obligatorios.',
        ]);

        $actualizadas = 0;
        $errores = [];

        foreach ($validated['configuraciones'] as $configData) {
            try {
                $configuracion = ConfiguracionSitio::find($configData['id']);

                // Validar valor según el tipo
                $valorValidado = $this->validarValorPorTipo($configData['valor'], $configuracion->tipo);

                if ($valorValidado === false) {
                    $errores[] = "Error en '{$configuracion->clave}': " . $this->getMensajeErrorTipo($configuracion->tipo);
                    continue;
                }

                $configuracion->update(['valor' => $valorValidado]);
                $actualizadas++;
            } catch (\Exception $e) {
                $errores[] = "Error actualizando configuración ID {$configData['id']}: " . $e->getMessage();
            }
        }

        Cache::forget('config_sitio');

        if (!empty($errores)) {
            return back()->withErrors(['general' => $errores])->with(
                'warning',
                "Se actualizaron {$actualizadas} configuraciones, pero hubo errores en algunas."
            );
        }

        return back()->with('success', "Se actualizaron {$actualizadas} configuraciones exitosamente.");
    }

    /**
     * Obtener configuración específica para APIs
     */
    public function getConfiguracion($clave)
    {
        $configuracion = ConfiguracionSitio::porClave($clave)->activo()->first();

        if (!$configuracion) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        return response()->json([
            'clave' => $configuracion->clave,
            'valor' => $configuracion->valor_casteado,
            'tipo' => $configuracion->tipo,
        ]);
    }

    /**
     * Obtener configuración completa para layouts
     */
    public function getConfiguracionLayout()
    {
        $configuracion = Cache::remember('config_sitio_layout', 3600, function () {
            return ConfiguracionSitio::getConfiguracionLayout();
        });

        return response()->json($configuracion);
    }

    /**
     * Exportar configuración como JSON
     */
    public function exportar()
    {
        $configuraciones = ConfiguracionSitio::activo()->get();

        $export = $configuraciones->mapWithKeys(function ($config) {
            return [$config->clave => [
                'valor' => $config->valor_casteado,
                'tipo' => $config->tipo,
                'descripcion' => $config->descripcion,
            ]];
        });

        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="configuracion_cicit.json"');
    }

    /**
     * Importar configuración desde JSON
     */
    public function importar(Request $request)
    {
        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:json', 'max:2048'],
        ], [
            'archivo.required' => 'Debe seleccionar un archivo.',
            'archivo.mimes' => 'El archivo debe ser de tipo JSON.',
            'archivo.max' => 'El archivo no puede ser mayor a 2MB.',
        ]);

        try {
            $contenido = file_get_contents($validated['archivo']->path());
            $configuraciones = json_decode($contenido, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['archivo' => 'El archivo JSON no es válido.']);
            }

            $importadas = 0;

            foreach ($configuraciones as $clave => $datos) {
                ConfiguracionSitio::updateOrCreate(
                    ['clave' => $clave],
                    [
                        'valor' => is_array($datos) ? $datos['valor'] : $datos,
                        'tipo' => is_array($datos) ? ($datos['tipo'] ?? 'string') : 'string',
                        'descripcion' => is_array($datos) ? ($datos['descripcion'] ?? null) : null,
                        'activo' => true,
                    ]
                );
                $importadas++;
            }

            Cache::forget('config_sitio');

            return back()->with('success', "Se importaron {$importadas} configuraciones exitosamente.");
        } catch (\Exception $e) {
            return back()->withErrors(['archivo' => 'Error procesando el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Validar valor según su tipo
     */
    private function validarValorPorTipo($valor, $tipo)
    {
        switch ($tipo) {
            case 'number':
                return is_numeric($valor) ? (string) $valor : false;

            case 'boolean':
                if (in_array(strtolower($valor), ['true', '1', 'yes', 'on'])) {
                    return 'true';
                } elseif (in_array(strtolower($valor), ['false', '0', 'no', 'off'])) {
                    return 'false';
                }
                return false;

            case 'json':
                if (is_array($valor)) {
                    return json_encode($valor);
                }
                $decoded = json_decode($valor);
                return json_last_error() === JSON_ERROR_NONE ? $valor : false;

            default: // string
                return (string) $valor;
        }
    }

    /**
     * Obtener mensaje de error por tipo
     */
    private function getMensajeErrorTipo($tipo)
    {
        return match ($tipo) {
            'number' => 'El valor debe ser un número válido.',
            'boolean' => 'El valor debe ser verdadero o falso (true/false, 1/0, yes/no).',
            'json' => 'El valor debe ser un JSON válido.',
            default => 'El valor no es válido para este tipo.',
        };
    }

    /**
     * Agrupar configuraciones por categorías
     */
    private function agruparConfiguraciones()
    {
        return Cache::remember('config_sitio_agrupadas', 1800, function () {
            return ConfiguracionSitio::getConfiguracionesEditables();
        });
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_configuraciones' => ConfiguracionSitio::count(),
            'configuraciones_activas' => ConfiguracionSitio::where('activo', true)->count(),
            'por_tipo' => ConfiguracionSitio::groupBy('tipo')
                ->selectRaw('tipo, count(*) as total')
                ->pluck('total', 'tipo'),
            'modo_mantenimiento' => ConfiguracionSitio::obtener('modo_mantenimiento', false),
            'ultima_actualizacion' => ConfiguracionSitio::latest('updated_at')->first()?->updated_at,
        ];

        return response()->json($stats);
    }
}
