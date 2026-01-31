<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Для всех остальных маршрутов тоже возвращаем welcome
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');

