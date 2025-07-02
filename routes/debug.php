<?php

use Illuminate\Support\Facades\Route;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Gestion;
use App\Models\TipoParticipante;

Route::get('/debug-cursos', function () {
    $cursosData = [
        'data' => Curso::with(['tutor:id,nombre,apellido', 'gestion:id,nombre', 'precios.tipoParticipante:id,codigo,descripcion'])
            ->withCount(['inscripciones', 'preinscripciones'])
            ->orderBy('created_at', 'desc')
            ->get(),
        'links' => [],
        'meta' => [
            'total' => Curso::count(),
            'per_page' => 50,
            'current_page' => 1
        ]
    ];

    $tutores = Usuario::where('rol', 'TUTOR')->where('activo', true)->get(['id', 'nombre', 'apellido']);
    $gestiones = Gestion::where('activo', true)->get(['id', 'nombre']);
    $tiposParticipante = TipoParticipante::where('activo', true)->get(['id', 'codigo', 'descripcion']);

    return response()->json([
        'cursosData' => $cursosData,
        'tutores' => $tutores,
        'gestiones' => $gestiones,
        'tiposParticipante' => $tiposParticipante,
    ]);
});
