<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

Route::apiResource('books', BookController::class);
