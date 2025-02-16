<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Redirect root URL to login page
Route::get('/', function () {
    return redirect('/login'); 
});

// Authentication Views
Route::view('/login', 'login')->name('login'); 

// Task Management View
Route::view('/tasks', 'tasks')->name('tasks'); 

// CSRF Token Route (for frontend security)
Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
})->name('csrf.token');

