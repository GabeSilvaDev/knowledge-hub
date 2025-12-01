<?php

namespace App\Providers;

use App\Cache\RedisCacheInvalidator;
use App\Contracts\CacheInvalidatorInterface;
use App\Contracts\SearchServiceInterface;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Like;
use App\Models\PersonalAccessToken;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use App\Observers\LikeObserver;
use App\Services\SearchService;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Override;

/**
 * Application Service Provider.
 *
 * Registers core application services and observers.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind(CacheInvalidatorInterface::class, RedisCacheInvalidator::class);
        $this->app->bind(
            SearchServiceInterface::class,
            SearchService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Article::observe(ArticleObserver::class);
        Comment::observe(CommentObserver::class);
        Like::observe(LikeObserver::class);
    }
}
