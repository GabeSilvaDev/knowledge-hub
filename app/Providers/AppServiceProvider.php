<?php

namespace App\Providers;

use App\Cache\RedisCacheInvalidator;
use App\Contracts\CacheInvalidatorInterface;
use App\Contracts\Neo4jRepositoryInterface;
use App\Contracts\RecommendationServiceInterface;
use App\Contracts\SearchServiceInterface;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\Like;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Observers\ArticleNeo4jObserver;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use App\Observers\FollowerNeo4jObserver;
use App\Observers\LikeNeo4jObserver;
use App\Observers\LikeObserver;
use App\Observers\UserNeo4jObserver;
use App\Repositories\Neo4jRepository;
use App\Services\RecommendationService;
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
        $this->app->bind(SearchServiceInterface::class, SearchService::class);
        $this->app->bind(Neo4jRepositoryInterface::class, Neo4jRepository::class);
        $this->app->bind(RecommendationServiceInterface::class, RecommendationService::class);
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

        User::observe(UserNeo4jObserver::class);
        Article::observe(ArticleNeo4jObserver::class);
        Follower::observe(FollowerNeo4jObserver::class);
        Like::observe(LikeNeo4jObserver::class);
    }
}
