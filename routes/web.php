<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas para Responsable
Route::middleware(['auth', 'role:RESPONSABLE'])->prefix('responsable')->name('responsable.')->group(function () {
    Route::resource('tipos-participante', \App\Http\Controllers\Responsable\TipoParticipanteController::class);

    // Rutas adicionales para TipoParticipante
    Route::post('tipos-participante/{tipoParticipante}/desactivar', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'desactivar'])->name('tipos-participante.desactivar');
    Route::post('tipos-participante/{tipoParticipante}/reactivar', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'reactivar'])->name('tipos-participante.reactivar');
    Route::post('tipos-participante/{tipoParticipante}/toggle-activo', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'toggleActivo'])->name('tipos-participante.toggle-activo');

    // APIs para selects
    Route::get('api/tipos-activos', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'getTiposActivos'])->name('api.tipos-activos');
    Route::get('api/tipos-estadisticas', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'estadisticas'])->name('api.tipos-estadisticas');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
