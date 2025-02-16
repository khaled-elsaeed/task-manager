<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes (Public)
Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login']);       

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Task Management
    Route::get('/tasks', [TaskController::class, 'index']);       
    Route::post('/tasks', [TaskController::class, 'store']);      
    Route::put('/tasks/{task}', [TaskController::class, 'update']); 
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); 

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']); 
});


