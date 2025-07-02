<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSitio;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Obtener configuración general del sitio
     */
    public function obtener()
    {
        $config = ConfiguracionSitio::first();

        if (!$config) {
            // Configuración por defecto si no existe
            $config = [
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
        } else {
            $config = $config->toArray();
            // Decodificar JSON de redes sociales si existe
            if (isset($config['redes_sociales']) && is_string($config['redes_sociales'])) {
                $config['redes_sociales'] = json_decode($config['redes_sociales'], true);
            }
        }

        return response()->json($config);
    }

    /**
     * Actualizar configuración del sitio (solo RESPONSABLE)
     */
    public function actualizar(Request $request)
    {
        $request->validate([
            'nombre_sitio' => 'required|string|max:255',
            'descripcion_sitio' => 'required|string|max:500',
            'color_primario' => 'required|string|max:7',
            'color_secundario' => 'required|string|max:7',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'direccion_fisica' => 'nullable|string|max:500',
            'redes_sociales' => 'nullable|array'
        ]);

        $config = ConfiguracionSitio::firstOrNew();

        $config->fill($request->only([
            'nombre_sitio',
            'descripcion_sitio',
            'color_primario',
            'color_secundario',
            'telefono_contacto',
            'email_contacto',
            'direccion_fisica'
        ]));

        if ($request->has('redes_sociales')) {
            $config->redes_sociales = json_encode($request->redes_sociales);
        }

        $config->save();

        return response()->json(['success' => true, 'config' => $config]);
    }
}
