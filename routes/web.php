<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

Route::get('/operario-login', [AuthController::class, 'showOperarioLogin'])->name('operario.login');
Route::post('/operario-login', [AuthController::class, 'validateOperario'])->name('operario.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::view('/register', 'auth.register')->name('register');
Route::get('/registro-pendiente', [UserController::class, 'pendingApproval'])->name('register.pending');
Route::post('/register', [UserController::class, 'register'])->name('register.submit');

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios');
    Route::patch('/usuarios/{user}/aprobar', [UserController::class, 'approve'])->name('admin.usuarios.approve');
    Route::delete('/usuarios/{user}/rechazar', [UserController::class, 'reject'])->name('admin.usuarios.reject');
});

Route::middleware('operario.auth')->group(function (): void {
    Route::get('/cliente', [ClienteController::class, 'index'])->name('cliente.index');
    Route::post('/cliente/buscar', [ClienteController::class, 'buscar'])->name('cliente.buscar');
    Route::post('/cliente/continuar', [PedidoController::class, 'continuar'])->name('cliente.continuar');
    Route::get('/pedido', [PedidoController::class, 'index'])->name('pedido.index');
    Route::get('/pedido/articulo/{codigo}', [PedidoController::class, 'buscarArticuloCodigo'])->name('pedido.articulo-codigo');
    Route::post('/pedido/buscar', [PedidoController::class, 'buscarArticulos'])->name('pedido.buscar-articulos');
    Route::post('/pedido/agregar-articulo', [PedidoController::class, 'agregarArticulo'])->name('pedido.agregar-articulo');
    Route::patch('/pedido/movimiento/{movimiento}', [PedidoController::class, 'editarMovimiento'])->name('pedido.editar-movimiento');
    Route::delete('/pedido/movimiento/{movimiento}', [PedidoController::class, 'borrarMovimiento'])->name('pedido.borrar-movimiento');
    Route::post('/pedido/actualizar', [PedidoController::class, 'actualizarCatalogo'])->name('pedido.actualizar');
    Route::post('/pedido/reiniciar', [PedidoController::class, 'reiniciar'])->name('pedido.reiniciar');
    Route::post('/pedido/terminar', [PedidoController::class, 'terminar'])->name('pedido.terminar');
    Route::get('/pedidos/proceso', [PedidoController::class, 'pedidosProceso'])->name('pedido.proceso');
    Route::get('/pedidos/aprobados', [PedidoController::class, 'pedidosAprobados'])->name('pedido.aprobados');
    Route::get('/pedidos/historico', [PedidoController::class, 'historicoPedidos'])->name('pedido.historico');
    Route::get('/pedidos/{pedido}/detalle', [PedidoController::class, 'detallePedido'])->name('pedido.detalle');
    Route::post('/pedidos/{pedido}/retomar', [PedidoController::class, 'retomarPedido'])->name('pedido.retomar');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'borrarPedido'])->name('pedido.borrar');
});
