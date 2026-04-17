<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Promotional Materials Backend',
    'status' => 'ok',
]));
