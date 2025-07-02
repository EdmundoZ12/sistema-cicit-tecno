<?php

namespace Database\Seeders;

use App\Models\ConfiguracionSitio;
use Illuminate\Database\Seeder;

class ConfiguracionSitioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Datos basados exactamente en el script SQL de CICIT
     */
    public function run(): void
    {
        $configuraciones = [
            [
                'clave' => 'nombre_sitio',
                'valor' => 'CICIT - Centro Integral de Certificación e Innovación Tecnológica',
                'tipo' => 'string',
                'descripcion' => 'Nombre oficial completo del centro',
                'activo' => true
            ],
            [
                'clave' => 'nombre_corto',
                'valor' => 'CICIT',
                'tipo' => 'string',
                'descripcion' => 'Nombre corto del centro',
                'activo' => true
            ],
            [
                'clave' => 'descripcion_sitio',
                'valor' => 'Centro Integral de Certificación e Innovación Tecnológica de la Universidad Autónoma Gabriel René Moreno',
                'tipo' => 'string',
                'descripcion' => 'Descripción oficial del centro',
                'activo' => true
            ],
            [
                'clave' => 'mision',
                'valor' => 'Formar profesionales competentes en tecnologías emergentes mediante cursos de certificación y programas de innovación tecnológica',
                'tipo' => 'string',
                'descripcion' => 'Misión del CICIT',
                'activo' => true
            ],
            [
                'clave' => 'vision',
                'valor' => 'Ser el centro de referencia en certificación e innovación tecnológica en Bolivia y la región',
                'tipo' => 'string',
                'descripcion' => 'Visión del CICIT',
                'activo' => true
            ],
            [
                'clave' => 'institucion_matriz',
                'valor' => 'Universidad Autónoma Gabriel René Moreno (UAGRM)',
                'tipo' => 'string',
                'descripcion' => 'Institución madre',
                'activo' => true
            ],
            [
                'clave' => 'facultad',
                'valor' => 'Facultad de Ingeniería en Ciencias de la Computación y Telecomunicaciones',
                'tipo' => 'string',
                'descripcion' => 'Facultad a la que pertenece',
                'activo' => true
            ],
            [
                'clave' => 'email_contacto',
                'valor' => 'cicit@uagrm.edu.bo',
                'tipo' => 'string',
                'descripcion' => 'Email oficial de contacto',
                'activo' => true
            ],
            [
                'clave' => 'email_certificados',
                'valor' => 'certificados@cicit.uagrm.edu.bo',
                'tipo' => 'string',
                'descripcion' => 'Email para consultas de certificados',
                'activo' => true
            ],
            [
                'clave' => 'telefono_contacto',
                'valor' => '+591 3 3336000',
                'tipo' => 'string',
                'descripcion' => 'Teléfono principal',
                'activo' => true
            ],
            [
                'clave' => 'telefono_whatsapp',
                'valor' => '+591 70000000',
                'tipo' => 'string',
                'descripcion' => 'WhatsApp para consultas',
                'activo' => true
            ],
            [
                'clave' => 'direccion',
                'valor' => 'Campus Universitario, Facultad de Ingeniería - UAGRM, Santa Cruz de la Sierra, Bolivia',
                'tipo' => 'string',
                'descripcion' => 'Dirección física completa',
                'activo' => true
            ],
            [
                'clave' => 'horario_atencion',
                'valor' => 'Lunes a Viernes: 8:00 - 18:00 | Sábados: 8:00 - 12:00',
                'tipo' => 'string',
                'descripcion' => 'Horarios de atención',
                'activo' => true
            ],
            [
                'clave' => 'areas_certificacion',
                'valor' => 'Desarrollo de Software, Ciberseguridad, Inteligencia Artificial, Redes y Telecomunicaciones, Innovación Tecnológica',
                'tipo' => 'string',
                'descripcion' => 'Áreas de certificación',
                'activo' => true
            ],
            [
                'clave' => 'modalidades',
                'valor' => 'Presencial, Semipresencial, Virtual',
                'tipo' => 'string',
                'descripcion' => 'Modalidades de cursos disponibles',
                'activo' => true
            ],
            [
                'clave' => 'logo_principal',
                'valor' => '/images/logos/cicit-logo.png',
                'tipo' => 'string',
                'descripcion' => 'Ruta del logo principal CICIT',
                'activo' => true
            ],
            [
                'clave' => 'logo_uagrm',
                'valor' => '/images/logos/uagrm-logo.png',
                'tipo' => 'string',
                'descripcion' => 'Ruta del logo UAGRM',
                'activo' => true
            ],
            [
                'clave' => 'logo_facultad',
                'valor' => '/images/logos/ficct-logo.png',
                'tipo' => 'string',
                'descripcion' => 'Ruta del logo FICCT',
                'activo' => true
            ],
            [
                'clave' => 'colores_institucionales',
                'valor' => '{"primario": "#7CB342", "secundario": "#2E7D32", "acento": "#FF6F00"}',
                'tipo' => 'json',
                'descripcion' => 'Colores oficiales del CICIT',
                'activo' => true
            ],
            [
                'clave' => 'redes_sociales',
                'valor' => '{"facebook": "CICIT.UAGRM", "instagram": "cicit_uagrm", "linkedin": "cicit-uagrm", "youtube": "CICIT UAGRM"}',
                'tipo' => 'json',
                'descripcion' => 'Redes sociales oficiales',
                'activo' => true
            ],
            [
                'clave' => 'modo_mantenimiento',
                'valor' => 'false',
                'tipo' => 'boolean',
                'descripcion' => 'Modo de mantenimiento del sitio',
                'activo' => true
            ],
            [
                'clave' => 'permitir_registro',
                'valor' => 'true',
                'tipo' => 'boolean',
                'descripcion' => 'Permitir auto-registro de usuarios',
                'activo' => true
            ],
            [
                'clave' => 'version_sistema',
                'valor' => '1.0.0',
                'tipo' => 'string',
                'descripcion' => 'Versión actual del sistema',
                'activo' => true
            ],
            [
                'clave' => 'desarrollado_por',
                'valor' => 'Grupo 7SA - Tecnología Web',
                'tipo' => 'string',
                'descripcion' => 'Equipo desarrollador',
                'activo' => true
            ]
        ];

        foreach ($configuraciones as $config) {
            ConfiguracionSitio::updateOrCreate(
                ['clave' => $config['clave']],
                $config
            );
        }
    }
}
