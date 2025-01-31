<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set'], 204);
});

Route::get('{any}', function () {
    return view('welcome');
})->where('any', '.*');
