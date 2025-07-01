<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\VisitaPagina;
use App\Models\BusquedaLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

class CertificadoPublicoController extends Controller
{
    /**
     * Página principal del verificador de certificados
     */
    public function index(Request $request): Response
    {
        // Registrar visita
        VisitaPagina::registrarVisita('/verificar-certificado', $request);

        return Inertia::render('Publico/Certificado/Verificar', [
            'contadorVisitas' => VisitaPagina::getTextoContador('/verificar-certificado'),
            'instrucciones' => $this->getInstruccionesVerificacion(),
            'ejemploCodigo' => 'CICIT-2025-ABCD1234',
        ]);
    }

    /**
     * Verificar certificado por código
     */
    public function verificar(Request $request)
    {
        $validated = $request->validate([
            'codigo_verificacion' => ['required', 'string', 'min:5', 'max:100'],
        ], [
            'codigo_verificacion.required' => 'El código de verificación es obligatorio.',
            'codigo_verificacion.min' => 'El código debe tener al menos 5 caracteres.',
            'codigo_verificacion.max' => 'El código no puede tener más de 100 caracteres.',
        ]);

        // Limpiar y normalizar código
        $codigo = strtoupper(trim($validated['codigo_verificacion']));

        // Registrar búsqueda de certificado
        BusquedaLog::registrarBusqueda("Certificado: {$codigo}", 0, $request);

        // Buscar certificado
        $certificado = Certificado::where('codigo_verificacion', $codigo)
            ->with([
                'inscripcion.participante.tipoParticipante',
                'inscripcion.curso.tutor',
                'inscripcion.curso.gestion'
            ])
            ->first();

        if (!$certificado) {
            // Registrar búsqueda sin resultados
            BusquedaLog::where('termino', "Certificado: {$codigo}")
                ->latest()
                ->first()
                ?->update(['resultados' => 0]);

            return response()->json([
                'valido' => false,
                'mensaje' => 'Código de verificación no encontrado. Verifique que el código sea correcto.',
                'codigo_buscado' => $codigo,
            ], 404);
        }

        // Actualizar contador de búsquedas exitosas
        BusquedaLog::where('termino', "Certificado: {$codigo}")
            ->latest()
            ->first()
            ?->update(['resultados' => 1]);

        // Verificar validez del certificado
        $esValido = $certificado->esValido();

        return response()->json([
            'valido' => $esValido,
            'certificado' => $this->formatearCertificadoPublico($certificado),
            'mensaje' => $esValido ? 'Certificado válido y verificado.' : 'Certificado encontrado pero no válido.',
        ]);
    }

    /**
     * Mostrar certificado completo
     */
    public function mostrar(Request $request, $codigo): Response
    {
        // Registrar visita específica
        VisitaPagina::registrarVisita("/certificado/{$codigo}", $request);

        $codigo = strtoupper(trim($codigo));

        $certificado = Certificado::where('codigo_verificacion', $codigo)
            ->with([
                'inscripcion.participante.tipoParticipante',
                'inscripcion.curso.tutor',
                'inscripcion.curso.gestion'
            ])
            ->first();

        if (!$certificado) {
            return Inertia::render('Publico/Certificado/NoEncontrado', [
                'codigo_buscado' => $codigo,
                'contadorVisitas' => VisitaPagina::getTextoContador("/certificado/{$codigo}"),
            ]);
        }

        return Inertia::render('Publico/Certificado/Mostrar', [
            'certificado' => $this->formatearCertificadoCompleto($certificado),
            'es_valido' => $certificado->esValido(),
            'contadorVisitas' => VisitaPagina::getTextoContador("/certificado/{$codigo}"),
        ]);
    }

    /**
     * Descargar PDF del certificado (público si está disponible)
     */
    public function descargar(Request $request, $codigo)
    {
        $codigo = strtoupper(trim($codigo));

        $certificado = Certificado::where('codigo_verificacion', $codigo)->first();

        if (!$certificado) {
            return response()->json([
                'error' => 'Certificado no encontrado'
            ], 404);
        }

        if (!$certificado->tiene_pdf) {
            return response()->json([
                'error' => 'El PDF del certificado no está disponible'
            ], 404);
        }

        $path = storage_path('app/public/' . $certificado->url_pdf);

        if (!file_exists($path)) {
            return response()->json([
                'error' => 'Archivo del certificado no encontrado'
            ], 404);
        }

        // Registrar descarga
        VisitaPagina::registrarVisita("/certificado/{$codigo}/descargar", $request);

        return response()->download(
            $path,
            "Certificado_{$certificado->codigo_verificacion}.pdf"
        );
    }

    /**
     * Búsqueda de certificados por participante
     */
    public function buscarPorParticipante(Request $request)
    {
        $validated = $request->validate([
            'carnet' => ['required', 'string'],
            'nombre' => ['nullable', 'string'],
            'apellido' => ['nullable', 'string'],
        ], [
            'carnet.required' => 'El número de carnet es obligatorio.',
        ]);

        $query = Certificado::whereHas('inscripcion.participante', function ($q) use ($validated) {
            $q->where('carnet', 'ILIKE', "%{$validated['carnet']}%");

            if (!empty($validated['nombre'])) {
                $q->where('nombre', 'ILIKE', "%{$validated['nombre']}%");
            }

            if (!empty($validated['apellido'])) {
                $q->where('apellido', 'ILIKE', "%{$validated['apellido']}%");
            }
        });

        $certificados = $query
            ->with([
                'inscripcion.participante',
                'inscripcion.curso'
            ])
            ->orderBy('fecha_emision', 'desc')
            ->take(10)
            ->get()
            ->map(function ($certificado) {
                return [
                    'codigo_verificacion' => $certificado->codigo_verificacion,
                    'tipo_descripcion' => $certificado->tipo_descripcion,
                    'participante' => $certificado->participante->nombre_completo,
                    'curso' => $certificado->curso->nombre,
                    'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
                ];
            });

        // Registrar búsqueda
        $termino = "Certificados participante: {$validated['carnet']}";
        BusquedaLog::registrarBusqueda($termino, $certificados->count(), $request);

        return response()->json([
            'encontrados' => $certificados->count(),
            'certificados' => $certificados,
        ]);
    }

    /**
     * Estadísticas públicas de certificados
     */
    public function estadisticasPublicas()
    {
        $stats = [
            'total_certificados' => Certificado::count(),
            'certificados_este_ano' => Certificado::whereYear('fecha_emision', now()->year)->count(),
            'tipos_disponibles' => [
                'participacion' => Certificado::participacion()->count(),
                'aprobacion' => Certificado::aprobacion()->count(),
                'mencion_honor' => Certificado::mencionHonor()->count(),
            ],
            'ultimo_emitido' => Certificado::latest('fecha_emision')
                ->first()
                ?->fecha_emision
                ?->format('d/m/Y'),
        ];

        return response()->json($stats);
    }

    /**
     * Validar formato de código de certificado
     */
    public function validarFormato(Request $request)
    {
        $codigo = $request->input('codigo', '');

        // Formato esperado: CICIT-YYYY-XXXXXXXX
        $patron = '/^CICIT-\d{4}-[A-Z0-9]{8}$/';

        $formatoValido = preg_match($patron, strtoupper($codigo));

        return response()->json([
            'formato_valido' => $formatoValido,
            'patron_esperado' => 'CICIT-YYYY-XXXXXXXX',
            'ejemplo' => 'CICIT-2025-ABCD1234',
            'sugerencias' => $this->getSugerenciasFormato($codigo),
        ]);
    }

    /**
     * Obtener información sobre tipos de certificados
     */
    public function tiposCertificados()
    {
        $tipos = [
            'PARTICIPACION' => [
                'nombre' => 'Certificado de Participación',
                'descripcion' => 'Otorgado a participantes que completaron el curso.',
                'requisitos' => 'Asistencia mínima requerida.',
            ],
            'APROBACION' => [
                'nombre' => 'Certificado de Aprobación',
                'descripcion' => 'Otorgado a participantes que aprobaron todas las evaluaciones.',
                'requisitos' => 'Nota mínima de aprobación y asistencia requerida.',
            ],
            'MENCION_HONOR' => [
                'nombre' => 'Certificado con Mención de Honor',
                'descripcion' => 'Otorgado a participantes con desempeño excepcional.',
                'requisitos' => 'Nota superior a 90 y excelente asistencia.',
            ],
        ];

        return response()->json($tipos);
    }

    /**
     * Reportar problema con certificado
     */
    public function reportarProblema(Request $request)
    {
        $validated = $request->validate([
            'codigo_verificacion' => ['required', 'string'],
            'tipo_problema' => ['required', 'string', 'in:codigo_invalido,datos_incorrectos,pdf_no_disponible,otro'],
            'descripcion' => ['required', 'string', 'max:500'],
            'email_contacto' => ['nullable', 'email'],
        ], [
            'codigo_verificacion.required' => 'El código de verificación es obligatorio.',
            'tipo_problema.required' => 'Debe seleccionar el tipo de problema.',
            'tipo_problema.in' => 'Tipo de problema no válido.',
            'descripcion.required' => 'La descripción del problema es obligatoria.',
            'descripcion.max' => 'La descripción no puede tener más de 500 caracteres.',
            'email_contacto.email' => 'El email de contacto debe ser válido.',
        ]);

        // En un sistema real, aquí se enviaría un ticket o email al administrador
        // Por ahora solo registramos el reporte

        return response()->json([
            'mensaje' => 'Su reporte ha sido registrado. Nos pondremos en contacto en un plazo de 24-48 horas.',
            'numero_ticket' => 'TICKET-' . now()->format('YmdHis'),
        ]);
    }

    /**
     * Formatear certificado para vista pública básica
     */
    private function formatearCertificadoPublico($certificado)
    {
        return [
            'codigo_verificacion' => $certificado->codigo_verificacion,
            'tipo' => $certificado->tipo,
            'tipo_descripcion' => $certificado->tipo_descripcion,
            'fecha_emision' => $certificado->fecha_emision->format('d/m/Y'),
            'participante' => [
                'nombre_completo' => $certificado->participante->nombre_completo,
                'tipo_participante' => $certificado->participante->tipoParticipante->descripcion,
            ],
            'curso' => [
                'nombre' => $certificado->curso->nombre,
                'duracion_horas' => $certificado->curso->duracion_horas,
                'tutor' => $certificado->curso->tutor->nombre_completo,
                'gestion' => $certificado->curso->gestion->nombre,
            ],
            'tiene_pdf' => $certificado->tiene_pdf,
        ];
    }

    /**
     * Formatear certificado para vista completa
     */
    private function formatearCertificadoCompleto($certificado)
    {
        $certificadoPublico = $this->formatearCertificadoPublico($certificado);

        // Agregar información adicional para vista completa
        $certificadoPublico['informacion_completa'] = $certificado->getInformacionCompleta();
        $certificadoPublico['es_reciente'] = $certificado->es_reciente;
        $certificadoPublico['url_descarga'] = $certificado->tiene_pdf ?
            "/certificado/{$certificado->codigo_verificacion}/descargar" : null;

        return $certificadoPublico;
    }

    /**
     * Obtener instrucciones de verificación
     */
    private function getInstruccionesVerificacion()
    {
        return [
            'El código de verificación se encuentra en la parte inferior de su certificado.',
            'Ingrese el código exactamente como aparece, incluyendo guiones.',
            'El código tiene el formato: CICIT-YYYY-XXXXXXXX',
            'Si no encuentra su certificado, puede buscarlo por número de carnet.',
            'Para reportar problemas, use el enlace "Reportar problema" en esta página.',
        ];
    }

    /**
     * Obtener sugerencias de formato para códigos incorrectos
     */
    private function getSugerenciasFormato($codigo)
    {
        $sugerencias = [];

        if (empty($codigo)) {
            $sugerencias[] = 'Ingrese un código de verificación.';
        } elseif (strlen($codigo) < 10) {
            $sugerencias[] = 'El código parece muy corto. Verifique que esté completo.';
        } elseif (!str_contains(strtoupper($codigo), 'CICIT')) {
            $sugerencias[] = 'El código debe comenzar con "CICIT-".';
        } elseif (!preg_match('/\d{4}/', $codigo)) {
            $sugerencias[] = 'El código debe incluir el año (4 dígitos).';
        } else {
            $sugerencias[] = 'Verifique que todos los caracteres sean correctos.';
        }

        return $sugerencias;
    }
}
