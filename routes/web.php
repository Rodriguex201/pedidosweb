<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    return back()->withInput(['email' => $validated['email']])->withErrors([
        'email' => 'Autenticación pendiente de integración con el módulo multiempresa (correo + contraseña + código de empresa).',
    ]);
})->name('login.submit');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'confirmed', 'min:8'],
        'company_code' => ['required', 'string', 'max:50'],
    ]);

    return redirect()->route('login')->with('status', 'Formulario válido. El flujo de registro multiempresa se conectará en una siguiente etapa.');
})->name('register.submit');

Route::view('/cliente', 'cliente.index')->name('cliente.index');
Route::view('/pedido', 'pedido.index')->name('pedido.index');
