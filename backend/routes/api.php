<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/menu', [CategoryController::class, 'menu']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show'])->whereNumber('id');
