<?php

namespace App\Providers;

use App\Contracts\ArticleRankingServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\CommentServiceInterface;
use App\Contracts\FeedServiceInterface;
use App\Contracts\FollowerServiceInterface;
use App\Contracts\LikeServiceInterface;
use App\Services\ArticleRankingService;
use App\Services\AuthService;
use App\Services\CommentService;
use App\Services\FeedService;
use App\Services\FollowerService;
use App\Services\LikeService;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Business Service Provider.
 *
 * Registers all business service bindings.
 */
class BusinessServiceProvider extends ServiceProvider
{
    /**
     * Register service bindings.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind(ArticleRankingServiceInterface::class, ArticleRankingService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CommentServiceInterface::class, CommentService::class);
        $this->app->bind(LikeServiceInterface::class, LikeService::class);
        $this->app->bind(FollowerServiceInterface::class, FollowerService::class);
        $this->app->bind(FeedServiceInterface::class, FeedService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
