<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ZonaController;
use App\Http\Controllers\EncomiendaController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\ReporteController;

// Ruta raíz → redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Login y logout
Route::get('/login', [UsuarioController::class, 'loginForm'])->name('login');
Route::post('/login', [UsuarioController::class, 'login'])->name('login.post');
Route::post('/logout', [UsuarioController::class, 'logout'])->name('logout');

// Rutas del OPERARIO
Route::middleware(['rol:operario,supervisor,administrador'])->group(function () {
    Route::get('/dashboard', [EncomiendaController::class, 'index'])->name('dashboard');
    Route::get('/almacen', [ZonaController::class, 'almacen'])->name('almacen'); // ← AGREGAR

    // Encomiendas
    Route::get('/encomiendas', [EncomiendaController::class, 'index'])->name('encomiendas.index');
    Route::get('/encomiendas/crear', [EncomiendaController::class, 'crear'])->name('encomiendas.crear');
    Route::post('/encomiendas', [EncomiendaController::class, 'registrar'])
        ->middleware('validar.encomienda')
        ->name('encomiendas.registrar');
    Route::get('/encomiendas/{id}', [EncomiendaController::class, 'ver'])->name('encomiendas.ver');
    Route::post('/encomiendas/{id}/estado', [EncomiendaController::class, 'cambiarEstado'])->name('encomiendas.estado');
    Route::post('/encomiendas/{id}/despachar', [EncomiendaController::class, 'despachar'])->name('encomiendas.despachar');
    Route::post('/encomiendas/{id}/reubicar', [EncomiendaController::class, 'reubicar'])->name('encomiendas.reubicar');
    Route::post('/encomiendas/{id}/danio', [EncomiendaController::class, 'notificarDanio'])->name('encomiendas.danio');
});

// Rutas del SUPERVISOR
Route::middleware(['rol:supervisor'])->group(function () {
    Route::get('/alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::post('/alertas/{id}/atender', [AlertaController::class, 'atender'])->name('alertas.atender');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');
});

// Rutas del ADMINISTRADOR
Route::middleware(['rol:administrador'])->group(function () {
    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'crear'])->name('usuarios.crear');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::post('/usuarios/{id}/estado', [UsuarioController::class, 'cambiarEstado'])->name('usuarios.estado');

    // Zonas
    Route::get('/zonas', [ZonaController::class, 'index'])->name('zonas.index');
    Route::get('/zonas/crear', [ZonaController::class, 'crear'])->name('zonas.crear');
    Route::post('/zonas', [ZonaController::class, 'store'])->name('zonas.store');
    Route::post('/zonas/{id}/estado', [ZonaController::class, 'cambiarEstado'])->name('zonas.estado');

    // Configuracion
    Route::get('/configuracion', [UsuarioController::class, 'configuracion'])->name('configuracion');
    Route::post('/configuracion', [UsuarioController::class, 'guardarConfiguracion'])->name('configuracion.guardar');
});