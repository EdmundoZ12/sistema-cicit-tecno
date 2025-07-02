<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Compartido\DashboardController;
use App\Http\Controllers\Compartido\BusquedaController;
use App\Http\Controllers\Compartido\ConfiguracionController;
use App\Http\Controllers\Compartido\VisitaController;
use App\Http\Controllers\Compartido\MenuController;

// =====================================================
// RUTAS PÚBLICAS (Sin autenticación)
// =====================================================

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Rutas públicas adicionales
Route::get('/cursos', function () {
    return Inertia::render('Publico/Cursos');
})->name('cursos.publicos');

Route::get('/sobre-nosotros', function () {
    return Inertia::render('Publico/SobreNosotros');
})->name('sobre-nosotros');

Route::get('/contacto', function () {
    return Inertia::render('Publico/Contacto');
})->name('contacto');

// Página "Acerca de"
Route::get('/acerca', function () {
    return Inertia::render('Publico/SobreNosotros');
})->name('acerca');

// Verificación de certificados (público)
Route::get('/certificados/verificar', function () {
    return Inertia::render('Publico/VerificarCertificado');
})->name('certificados.verificar');

Route::post('/certificados/verificar', function () {
    return response()->json([
        'success' => false,
        'message' => 'Funcionalidad en desarrollo'
    ]);
})->name('certificados.verificar.post');

// =====================================================
// RUTAS COMPARTIDAS (Múltiples roles autenticados)
// =====================================================

// Dashboard dinámico según rol
Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Sistema de búsquedas (Requisito 9)
Route::middleware(['auth'])->group(function () {
    Route::post('/buscar', [BusquedaController::class, 'buscar'])->name('busqueda.buscar');
    Route::get('/busqueda-rapida', [BusquedaController::class, 'busquedaRapida'])->name('busqueda.rapida');
    Route::get('/sugerencias', [BusquedaController::class, 'sugerencias'])->name('busqueda.sugerencias');
});

// Búsqueda pública (sin autenticación)
Route::post('/busqueda-publica', [BusquedaController::class, 'buscar'])->name('busqueda.publica');

// Sistema de temas y accesibilidad (Requisito 5)
Route::middleware(['auth'])->prefix('configuracion')->name('configuracion.')->group(function () {
    Route::get('/', [ConfiguracionController::class, 'index'])->name('index');
    Route::post('/tema', [ConfiguracionController::class, 'actualizarTema'])->name('tema.actualizar');
    Route::post('/fuente/cambiar', [ConfiguracionController::class, 'cambiarTamanoFuente'])->name('fuente.cambiar');
    Route::post('/contraste/alternar', [ConfiguracionController::class, 'alternarAltoContraste'])->name('contraste.alternar');
});

// CSS dinámico y tema actual (accesible sin autenticación)
Route::get('/css/personalizado', [ConfiguracionController::class, 'cssPersonalizado'])->name('css.personalizado');
Route::get('/tema-actual', [ConfiguracionController::class, 'temaActual'])->name('tema.actual');

// Menú dinámico (Requisito 3)
Route::prefix('menu')->name('menu.')->group(function () {
    // Público
    Route::get('/obtener', [MenuController::class, 'obtenerMenu'])->name('obtener');
    Route::post('/verificar-acceso', [MenuController::class, 'verificarAcceso'])->name('verificar.acceso');

    // Solo para usuarios autenticados
    Route::middleware(['auth'])->group(function () {
        Route::post('/breadcrumbs', [MenuController::class, 'obtenerBreadcrumbs'])->name('breadcrumbs');
    });

    // Solo para RESPONSABLE
    Route::middleware(['auth', 'role:RESPONSABLE'])->group(function () {
        Route::get('/estadisticas', [MenuController::class, 'estadisticasMenu'])->name('estadisticas');
        Route::post('/limpiar-cache', [MenuController::class, 'limpiarCacheMenu'])->name('limpiar.cache');
    });
});

// Contador de visitas (Requisito 7)
Route::prefix('visitas')->name('visitas.')->group(function () {
    // Públicas
    Route::get('/contador/{pagina?}', [VisitaController::class, 'contadorPagina'])->name('contador');
    Route::get('/pie-pagina', [VisitaController::class, 'datosPiePagina'])->name('pie.pagina');
    Route::get('/estadisticas-generales', [VisitaController::class, 'estadisticasGenerales'])->name('estadisticas.generales');

    // Solo para usuarios autenticados
    Route::middleware(['auth'])->group(function () {
        Route::get('/en-linea', [VisitaController::class, 'visitantesEnLinea'])->name('en.linea');
        Route::get('/mas-visitadas', [VisitaController::class, 'paginasMasVisitadas'])->name('mas.visitadas');
    });

    // Solo para RESPONSABLE
    Route::middleware(['auth', 'role:RESPONSABLE'])->group(function () {
        Route::get('/reportes', [VisitaController::class, 'reportes'])->name('reportes');
    });
});

// Estadísticas de búsquedas (solo para RESPONSABLE)
Route::middleware(['auth', 'role:RESPONSABLE'])->group(function () {
    Route::get('/busqueda/estadisticas', [BusquedaController::class, 'estadisticas'])->name('busqueda.estadisticas');
});

// =====================================================
// RUTAS POR ROLES ESPECÍFICOS
// =====================================================

// Rutas para Responsable
Route::middleware(['auth', 'role:RESPONSABLE'])->prefix('responsable')->name('responsable.')->group(function () {
    Route::resource('tipos-participante', \App\Http\Controllers\Responsable\TipoParticipanteController::class);
    Route::resource('gestiones', \App\Http\Controllers\Responsable\GestionController::class);
    Route::resource('cursos', \App\Http\Controllers\Responsable\CursoController::class);

    // Rutas adicionales para TipoParticipante
    Route::post('tipos-participante/{tipoParticipante}/desactivar', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'desactivar'])->name('tipos-participante.desactivar');
    Route::post('tipos-participante/{tipoParticipante}/reactivar', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'reactivar'])->name('tipos-participante.reactivar');
    Route::post('tipos-participante/{tipoParticipante}/toggle-activo', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'toggleActivo'])->name('tipos-participante.toggle-activo');

    // Rutas adicionales para Cursos
    Route::post('cursos/upload-image', [\App\Http\Controllers\Responsable\CursoController::class, 'uploadImage'])->name('cursos.upload-image');
    Route::post('cursos/{curso}/desactivar', [\App\Http\Controllers\Responsable\CursoController::class, 'desactivar'])->name('cursos.desactivar');
    Route::post('cursos/{curso}/reactivar', [\App\Http\Controllers\Responsable\CursoController::class, 'reactivar'])->name('cursos.reactivar');
    Route::post('cursos/{curso}/toggle-activo', [\App\Http\Controllers\Responsable\CursoController::class, 'toggleActivo'])->name('cursos.toggle-activo');

    // APIs para selects
    Route::get('api/tipos-activos', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'getTiposActivos'])->name('api.tipos-activos');
    Route::get('api/tipos-estadisticas', [\App\Http\Controllers\Responsable\TipoParticipanteController::class, 'estadisticas'])->name('api.tipos-estadisticas');
    Route::get('api/cursos-activos', [\App\Http\Controllers\Responsable\CursoController::class, 'getCursosActivos'])->name('api.cursos-activos');
    Route::get('api/cursos-estadisticas', [\App\Http\Controllers\Responsable\CursoController::class, 'estadisticas'])->name('api.cursos-estadisticas');

    // Rutas adicionales del responsable
    Route::get('usuarios', [\App\Http\Controllers\Responsable\ResponsableController::class, 'usuarios'])->name('usuarios');

    // Dashboard específico
    Route::get('/dashboard', [\App\Http\Controllers\Responsable\ResponsableController::class, 'dashboard'])->name('dashboard');

    // Rutas de usuarios (dentro del grupo responsable)
    Route::get('usuarios-data', [\App\Http\Controllers\Responsable\ResponsableController::class, 'getUsuariosData'])->name('usuarios.data');
    Route::post('usuarios', [\App\Http\Controllers\Responsable\ResponsableController::class, 'storeUsuario'])->name('usuarios.store');
    Route::put('usuarios/{usuario}', [\App\Http\Controllers\Responsable\ResponsableController::class, 'updateUsuario'])->name('usuarios.update');
    Route::delete('usuarios/{usuario}', [\App\Http\Controllers\Responsable\ResponsableController::class, 'deleteUsuario'])->name('usuarios.delete');
    Route::put('usuarios/{usuario}/toggle-status', [\App\Http\Controllers\Responsable\ResponsableController::class, 'toggleUserStatus'])->name('usuarios.toggle-status');
    Route::post('usuarios/{usuario}/reset-password', [\App\Http\Controllers\Responsable\ResponsableController::class, 'resetUserPassword'])->name('usuarios.reset-password');

    // NOTA: La ruta 'cursos' está comentada porque conflicta con el resource route de CursoController
    // Route::get('cursos', [\App\Http\Controllers\Responsable\ResponsableController::class, 'cursos'])->name('cursos');
    Route::get('estadisticas', [\App\Http\Controllers\Responsable\ResponsableController::class, 'estadisticas'])->name('estadisticas');
    Route::get('configuracion', [\App\Http\Controllers\Responsable\ResponsableController::class, 'configuracion'])->name('configuracion');
    Route::get('pagos', [\App\Http\Controllers\Responsable\ResponsableController::class, 'pagos'])->name('pagos');
    Route::get('reportes', [\App\Http\Controllers\Responsable\ResponsableController::class, 'reportes'])->name('reportes');
    Route::get('database', [\App\Http\Controllers\Responsable\ResponsableController::class, 'database'])->name('database');
});

// Rutas para Tutor (ya existentes)
Route::middleware(['auth', 'role:TUTOR'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::resource('notas', \App\Http\Controllers\Tutor\NotaController::class);

    // Rutas adicionales para notas
    Route::post('/notas/calificar', [\App\Http\Controllers\Tutor\NotaController::class, 'calificar'])->name('notas.calificar');
    Route::post('/notas/calificar-masivo', [\App\Http\Controllers\Tutor\NotaController::class, 'calificarMasivo'])->name('notas.calificar-masivo');
    Route::post('/notas/aplicar-nota-todos', [\App\Http\Controllers\Tutor\NotaController::class, 'aplicarNotaATodos'])->name('notas.aplicar-todos');
    Route::delete('/notas/{nota}/eliminar', [\App\Http\Controllers\Tutor\NotaController::class, 'eliminarNota'])->name('notas.eliminar');
    Route::get('/estadisticas-dashboard', [\App\Http\Controllers\Tutor\NotaController::class, 'getEstadisticasDashboard'])->name('estadisticas.dashboard');
});

// =====================================================
// RUTAS DE PERFIL DE USUARIO
// =====================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\Profile\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\Profile\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Profile\ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [\App\Http\Controllers\Profile\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =====================================================
// RUTAS API
// =====================================================

// API para temas y configuración
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Temas disponibles desde la base de datos
    Route::get('/temas/disponibles', [ConfiguracionController::class, 'temasDisponibles'])->name('api.temas.disponibles');

    // Configuración del usuario actual
    Route::get('/configuracion/usuario', [ConfiguracionController::class, 'configuracionUsuario'])->name('api.configuracion.usuario');
    Route::post('/configuracion/usuario', [ConfiguracionController::class, 'guardarConfiguracionUsuario'])->name('api.configuracion.usuario.guardar');

    // Registrar visita de página
    Route::post('/visitas/registrar', [VisitaController::class, 'registrarVisita'])->name('api.visitas.registrar');
    Route::get('/visitas/contador/{pagina?}', [VisitaController::class, 'contadorPagina'])->name('api.visitas.contador');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/settings.php';
