<?php

use Illuminate\Support\Facades\Route;

route::get('/',function(){
    return view('login');
});
Route::get('/login', function () {
    return view('login');
});

Route::get('/tasks', function () {
    return view('tasks');
});

Route::get('/csrf-token', function () {
    return response()->json(['csrfToken' => csrf_token()]);
});