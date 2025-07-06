<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

// Authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

// Public routes (no authentication required)
Route::get('books', [BookController::class, 'index']);
Route::get('books/filter', [BookController::class, 'filter']);
Route::get('books/search', [BookController::class, 'search']);
Route::get('books/{id}', [BookController::class, 'show']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/filter', [CategoryController::class, 'filter']);
Route::get('categories/{id}', [CategoryController::class, 'show']);
Route::get('categories/{id}/books', [CategoryController::class, 'getBooks']);

// Protected routes (authentication required)
Route::middleware('auth:api')->group(function () {
    // Books - create, update, delete operations
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{id}', [BookController::class, 'update']);
    Route::delete('books/{id}', [BookController::class, 'destroy']);
    
    // Categories - admin-only create, update, delete operations
    Route::middleware('role:admin')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
        Route::apiResource('users', UserController::class);
    });
}); 