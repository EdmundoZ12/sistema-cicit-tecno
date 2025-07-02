<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Preinscripciones - CICIT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #2563eb;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .info-section {
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label, .info-value {
            display: table-cell;
            padding: 3px 8px;
            border: 1px solid #e2e8f0;
        }
        
        .info-label {
            font-weight: bold;
            background-color: #f8fafc;
            width: 25%;
            color: #374151;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 8px;
        }
        
        th, td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            text-align: left;
        }
        
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            color: #374151;
        }
        
        .status-pendiente {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-aprobada {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-rechazada {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .no-data {
            text-align: center;
            padding: 30px;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>CENTRO INTEGRAL DE CERTIFICACIÓN E INNOVACIÓN TECNOLÓGICA</h1>
        <h2>Universidad Autónoma Gabriel René Moreno</h2>
        <p><strong>REPORTE DE PREINSCRIPCIONES</strong></p>
    </div>

    <!-- Información del Reporte -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Fecha de Generación:</div>
                <div class="info-value">{{ $fecha_generacion }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total de Registros:</div>
                <div class="info-value">{{ count($preinscripciones) }}</div>
            </div>
            @if(!empty($filtros['estado']))
            <div class="info-row">
                <div class="info-label">Filtro por Estado:</div>
                <div class="info-value">{{ strtoupper($filtros['estado']) }}</div>
            </div>
            @endif
            @if(!empty($filtros['search']))
            <div class="info-row">
                <div class="info-label">Búsqueda:</div>
                <div class="info-value">{{ $filtros['search'] }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(count($preinscripciones) > 0)
    <!-- Tabla de Preinscripciones -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 20%;">Participante</th>
                <th style="width: 12%;">Carnet</th>
                <th style="width: 25%;">Curso</th>
                <th style="width: 15%;">Instructor</th>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($preinscripciones as $preinscripcion)
            <tr>
                <td>#{{ str_pad($preinscripcion->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $preinscripcion->participante->nombre }} {{ $preinscripcion->participante->apellido }}</td>
                <td>{{ $preinscripcion->participante->carnet }}</td>
                <td>{{ $preinscripcion->curso->nombre }}</td>
                <td>{{ $preinscripcion->curso->tutor->nombre_completo }}</td>
                <td>{{ $preinscripcion->fecha_preinscripcion->format('d/m/Y') }}</td>
                <td>
                    @if($preinscripcion->estado === 'PENDIENTE')
                        <span class="status-pendiente">PENDIENTE</span>
                    @elseif($preinscripcion->estado === 'APROBADA')
                        <span class="status-aprobada">APROBADA</span>
                    @elseif($preinscripcion->estado === 'RECHAZADA')
                        <span class="status-rechazada">RECHAZADA</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumen por Estado -->
    <div style="margin-top: 20px;">
        <h3 style="font-size: 12px; margin-bottom: 10px; color: #374151;">Resumen por Estado</h3>
        <div class="info-grid" style="width: 50%;">
            <div class="info-row">
                <div class="info-label">Pendientes:</div>
                <div class="info-value">{{ $preinscripciones->where('estado', 'PENDIENTE')->count() }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Aprobadas:</div>
                <div class="info-value">{{ $preinscripciones->where('estado', 'APROBADA')->count() }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Rechazadas:</div>
                <div class="info-value">{{ $preinscripciones->where('estado', 'RECHAZADA')->count() }}</div>
            </div>
        </div>
    </div>
    @else
    <div class="no-data">
        <h3>No se encontraron preinscripciones</h3>
        <p>No hay registros que coincidan con los filtros aplicados.</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Documento generado automáticamente por el Sistema CICIT - {{ $fecha_generacion }}</p>
        <p>Centro Integral de Certificación e Innovación Tecnológica - Universidad Autónoma Gabriel René Moreno</p>
    </div>
</body>
</html>
