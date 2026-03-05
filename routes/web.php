<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::get('/registro-pendiente', [UserController::class, 'pendingApproval'])->name('register.pending');
Route::post('/register', [UserController::class, 'register'])->name('register.submit');

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    return back()->withInput(['email' => $validated['email']])->withErrors([
        'email' => 'Autenticación pendiente de integración con el módulo multiempresa (correo + contraseña + código de empresa).',
    ]);
})->name('login.submit');

Route::get('/usuarios', [UserController::class, 'index'])->name('admin.usuarios');
Route::patch('/usuarios/{user}/aprobar', [UserController::class, 'approve'])->name('admin.usuarios.approve');

Route::view('/cliente', 'cliente.index')->name('cliente.index');
Route::view('/pedido', 'pedido.index')->name('pedido.index');
