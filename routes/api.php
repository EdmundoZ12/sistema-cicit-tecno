<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API simples sin middleware problemático
Route::get('/notificaciones/no-leidas', function () {
    return response()->json(['notifications' => [], 'count' => 0]);
});

Route::get('/temas/disponibles', function () {
    return response()->json([
        'themes' => [
            ['id' => 'light', 'name' => 'Claro'],
            ['id' => 'dark', 'name' => 'Oscuro'],
            ['id' => 'blue', 'name' => 'Azul UAGRM']
        ]
    ]);
});

Route::get('/configuracion/usuario', function () {
    return response()->json([
        'theme' => 'light',
        'fontSize' => 'medium',
        'highContrast' => false
    ]);
});

Route::get('/estadisticas/publicas', function () {
    return response()->json([
        'total_cursos' => 25,
        'estudiantes_certificados' => 1500,
        'instructores' => 15,
        'horas_capacitacion' => 50000
    ]);
});
