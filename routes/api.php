<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleRankingController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\TrackArticleView;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/articles/popular', [ArticleController::class, 'popular']);
Route::get('/articles/ranking', [ArticleRankingController::class, 'index']);
Route::get('/articles/ranking/statistics', [ArticleRankingController::class, 'statistics']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/revoke-all', [AuthController::class, 'revokeAll']);

    Route::post('/articles/ranking/sync', [ArticleRankingController::class, 'sync']);

    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}/ranking', [ArticleRankingController::class, 'show']);
});

Route::get('/articles/{article}', [ArticleController::class, 'show'])->middleware(TrackArticleView::class);
