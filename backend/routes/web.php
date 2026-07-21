<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'News Portal API',
    'endpoints' => [
        'menu' => '/api/menu',
        'categories' => '/api/categories',
        'news' => '/api/news',
    ],
]));
