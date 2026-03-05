<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/cliente', 'cliente.index')->name('cliente.index');
Route::view('/pedido', 'pedido.index')->name('pedido.index');
