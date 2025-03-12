<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ArticleFiltersController;
use Illuminate\Support\Facades\Route;


Route::get('/filters',[ArticleFiltersController::class,'index']);
Route::get('/articles',[ArticleController::class,'index']);
Route::get('/articles/{slug}',[ArticleController::class,'show']);
