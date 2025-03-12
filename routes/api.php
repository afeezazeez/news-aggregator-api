<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleFiltersController;

use Illuminate\Support\Facades\Route;

Route::get('/articles',[ArticleController::class,'index']);
Route::get('/filters',[ArticleFiltersController::class,'index']);
