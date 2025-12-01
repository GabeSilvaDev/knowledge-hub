<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleRankingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\TrackArticleView;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/feed', [FeedController::class, 'index']);
Route::get('/feed/public', [FeedController::class, 'public']);

Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete']);

Route::get('/articles/popular', [ArticleController::class, 'popular']);
Route::get('/articles/ranking', [ArticleRankingController::class, 'index']);
Route::get('/articles/ranking/statistics', [ArticleRankingController::class, 'statistics']);
Route::get('/articles/{article}', [ArticleController::class, 'show'])->middleware(TrackArticleView::class);
Route::get('/articles/{articleId}/related', [RecommendationController::class, 'related']);

Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/users/{user}/followers', [FollowerController::class, 'followers']);
Route::get('/users/{user}/following', [FollowerController::class, 'following']);

Route::get('/articles/{articleId}/comments', [CommentController::class, 'index']);

Route::get('/recommendations/authors', [RecommendationController::class, 'authors']);
Route::get('/recommendations/statistics', [RecommendationController::class, 'statistics']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/me', [UserController::class, 'update']);

    Route::get('/feed/personalized', [FeedController::class, 'personalized']);

    Route::post('/articles/ranking/sync', [ArticleRankingController::class, 'sync']);
    Route::post('/search/sync', [SearchController::class, 'sync']);
    Route::post('/articles', [ArticleController::class, 'store'])->middleware('throttle:10,1');
    Route::put('/articles/{article}', [ArticleController::class, 'update']);
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{article}/ranking', [ArticleRankingController::class, 'show']);

    Route::post('/comments', [CommentController::class, 'store'])->middleware('throttle:30,1');
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/articles/{article}/like', [LikeController::class, 'toggle'])->middleware('throttle:60,1');
    Route::get('/articles/{article}/like/check', [LikeController::class, 'check']);

    Route::post('/users/{user}/follow', [FollowerController::class, 'toggle'])->middleware('throttle:30,1');
    Route::get('/users/{user}/follow/check', [FollowerController::class, 'check']);

    Route::get('/recommendations/users', [RecommendationController::class, 'users']);
    Route::get('/recommendations/articles', [RecommendationController::class, 'articles']);
    Route::get('/recommendations/topics', [RecommendationController::class, 'topics']);
    Route::post('/recommendations/sync', [RecommendationController::class, 'sync']);
});
