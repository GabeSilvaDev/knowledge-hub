<?php

namespace App\Providers;

use App\Cache\RedisCacheInvalidator;
use App\Contracts\CacheInvalidatorInterface;
use App\Models\Article;
use App\Models\PersonalAccessToken;
use App\Observers\ArticleObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->bind(CacheInvalidatorInterface::class, RedisCacheInvalidator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Article::observe(ArticleObserver::class);
    }
}
