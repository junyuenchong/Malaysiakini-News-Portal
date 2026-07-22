<?php

use App\Modules\Category\Http\Controllers\CategoryController;
use App\Modules\News\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/menu', [CategoryController::class, 'menu']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}/news', [CategoryController::class, 'news'])->whereNumber('id');
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{id}', [NewsController::class, 'show'])->whereNumber('id');
