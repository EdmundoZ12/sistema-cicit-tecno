<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Preinscripción - CICIT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            color: #64748b;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #64748b;
            font-size: 14px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            border-left: 4px solid #2563eb;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label, .info-value {
            display: table-cell;
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-label {
            font-weight: bold;
            background-color: #f8fafc;
            width: 30%;
            color: #374151;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .highlight-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        
        .highlight-box .id {
            font-size: 24px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .highlight-box .label {
            font-size: 14px;
            color: #78350f;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }
        
        .instructions {
            background-color: #e0f2fe;
            border-left: 4px solid #0891b2;
            padding: 15px;
            margin: 20px 0;
        }
        
        .instructions h3 {
            color: #0e7490;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            margin-left: 20px;
            color: #0f172a;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 11px;
        }
        
        .qr-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .qr-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>CENTRO INTEGRAL DE CERTIFICACIÓN E INNOVACIÓN TECNOLÓGICA</h1>
            <h2>Universidad Autónoma Gabriel René Moreno</h2>
            <p><strong>COMPROBANTE DE PREINSCRIPCIÓN</strong></p>
        </div>

        <!-- ID de Preinscripción Destacado -->
        <div class="highlight-box">
            <div class="id">ID: {{ str_pad($preinscripcion->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="label">Código de Preinscripción</div>
        </div>

        <!-- Información del Participante -->
        <div class="section">
            <div class="section-title">DATOS DEL PARTICIPANTE</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nombre Completo:</div>
                    <div class="info-value">{{ $participante->nombre }} {{ $participante->apellido }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Carnet de Identidad:</div>
                    <div class="info-value">{{ $participante->carnet }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Correo Electrónico:</div>
                    <div class="info-value">{{ $participante->email }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Teléfono:</div>
                    <div class="info-value">{{ $participante->telefono ?? 'No especificado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Universidad:</div>
                    <div class="info-value">{{ $participante->universidad ?? 'No especificado' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tipo de Participante:</div>
                    <div class="info-value">{{ $participante->tipoParticipante->descripcion }}</div>
                </div>
            </div>
        </div>

        <!-- Información del Curso -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DEL CURSO</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nombre del Curso:</div>
                    <div class="info-value">{{ $curso->nombre }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Descripción:</div>
                    <div class="info-value">{{ $curso->descripcion }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Instructor:</div>
                    <div class="info-value">{{ $tutor->nombre_completo }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Duración:</div>
                    <div class="info-value">{{ $curso->duracion_horas }} horas</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nivel:</div>
                    <div class="info-value">{{ $curso->nivel }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fecha de Inicio:</div>
                    <div class="info-value">{{ $curso->fecha_inicio->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Fecha de Finalización:</div>
                    <div class="info-value">{{ $curso->fecha_fin->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Aula:</div>
                    <div class="info-value">{{ $curso->aula }}</div>
                </div>
            </div>
        </div>

        <!-- Información de la Preinscripción -->
        <div class="section">
            <div class="section-title">DETALLES DE LA PREINSCRIPCIÓN</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Fecha de Preinscripción:</div>
                    <div class="info-value">{{ $preinscripcion->fecha_preinscripcion->format('d/m/Y H:i') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Estado:</div>
                    <div class="info-value">
                        <span class="status-pending">{{ strtoupper($preinscripcion->estado) }}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Costo del Curso:</div>
                    <div class="info-value">{{ $precio ? $precio->precio_formateado : 'Por definir' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Observaciones:</div>
                    <div class="info-value">{{ $preinscripcion->observaciones ?? 'Ninguna' }}</div>
                </div>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="instructions">
            <h3>PRÓXIMOS PASOS PARA COMPLETAR SU INSCRIPCIÓN:</h3>
            <ol>
                <li><strong>Guarde este comprobante:</strong> Será necesario para el proceso de inscripción formal.</li>
                <li><strong>Contacto administrativo:</strong> El personal administrativo se pondrá en contacto con usted para coordinar el pago.</li>
                <li><strong>Realice el pago:</strong> Una vez confirmado el pago, su inscripción será oficial.</li>
                <li><strong>Confirmación final:</strong> Recibirá un correo de confirmación con todos los detalles del curso.</li>
                <li><strong>Asistencia al curso:</strong> Preséntese el día de inicio en el aula indicada.</li>
            </ol>
        </div>

        <!-- Información de Contacto -->
        <div class="section">
            <div class="section-title">INFORMACIÓN DE CONTACTO</div>
            <div style="text-align: center; padding: 15px;">
                <p><strong>Centro Integral de Certificación e Innovación Tecnológica (CICIT)</strong></p>
                <p>Universidad Autónoma Gabriel René Moreno</p>
                <p>Para consultas sobre su preinscripción, mencione el código: <strong>{{ str_pad($preinscripcion->id, 6, '0', STR_PAD_LEFT) }}</strong></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Documento generado automáticamente el {{ $fecha_generacion }}</p>
            <p>Este documento es válido como comprobante de preinscripción</p>
            <p><strong>IMPORTANTE:</strong> La preinscripción no garantiza un cupo hasta completar el pago correspondiente</p>
        </div>
    </div>
</body>
</html>
