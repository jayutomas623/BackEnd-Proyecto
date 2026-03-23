<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReporteController;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/categorias', [MenuController::class, 'getCategories']);
Route::get('/productos', function () {
    return \App\Models\Product::with(['category', 'insumoRetail', 'modificadores'])
        ->where('disponibilidad', true)
        ->get();
});

Route::middleware('auth:sanctum')->group(function () {

    // ── Pedidos ──
    Route::get('/pedidos',                [OrderController::class, 'index']);
    Route::post('/pedidos',               [OrderController::class, 'store']);
    Route::put('/pedidos/{id}',           [OrderController::class, 'update']);        
    Route::put('/pedidos/{id}/estado',    [OrderController::class, 'updateStatus']);
    Route::get('/mis-pedidos',            [OrderController::class, 'myOrders']);
    Route::post('/pedidos/{id}/calificar',[OrderController::class, 'rateOrder']);


    Route::get('/menu', function () {
        return \App\Models\Product::with(['category', 'insumoRetail', 'modificadores'])
            ->where('disponibilidad', true)
            ->get();
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/insumos',                    [InsumoController::class, 'index']);
    Route::post('/insumos',                   [InsumoController::class, 'store']);
    Route::put('/insumos/{id}/cantidad',      [InsumoController::class, 'actualizarCantidad']);
    Route::put('/insumos/{id}/estado',        [InsumoController::class, 'actualizarEstado']);
    Route::delete('/insumos/{id}',            [InsumoController::class, 'destroy']);

    Route::get('/admin/productos',            [ProductController::class, 'index']);
    Route::post('/admin/productos',           [ProductController::class, 'store']);
    Route::put('/admin/productos/{id}',       [ProductController::class, 'update']);
    Route::delete('/admin/productos/{id}',    [ProductController::class, 'destroy']);

    Route::get('/admin/usuarios',             [UserController::class, 'index']);
    Route::post('/admin/usuarios',            [UserController::class, 'store']);
    Route::put('/admin/usuarios/{id}',        [UserController::class, 'update']);
    Route::get('/admin/roles',                [UserController::class, 'getRoles']);

    Route::get('/admin/estadisticas',         [DashboardController::class, 'getStats']);

    Route::get('/admin/categorias',           [CategoryController::class, 'index']);
    Route::post('/admin/categorias',          [CategoryController::class, 'store']);
    Route::put('/admin/categorias/{id}',      [CategoryController::class, 'update']);
    Route::delete('/admin/categorias/{id}',   [CategoryController::class, 'destroy']);

    Route::get('/admin/reporte/cierre-caja',  [ReporteController::class, 'cierreCaja']);
    Route::get('/admin/reporte/historial',    [ReporteController::class, 'historialPedidos']);
    Route::get('/admin/reporte/cajeros',      [ReporteController::class, 'cajeros']);
});