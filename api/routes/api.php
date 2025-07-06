<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;

Route::apiResource('books', BookController::class);
Route::apiResource('categories', CategoryController::class);
Route::get('categories/{id}/books', [CategoryController::class, 'getBooks']); 