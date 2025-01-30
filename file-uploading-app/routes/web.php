<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/api.php';

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set'], 204);
});
