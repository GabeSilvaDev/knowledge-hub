<?php

namespace App\Providers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\CommentRepositoryInterface;
use App\Contracts\FeedRepositoryInterface;
use App\Contracts\FollowerRepositoryInterface;
use App\Contracts\LikeRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\CommentRepository;
use App\Repositories\FeedRepository;
use App\Repositories\FollowerRepository;
use App\Repositories\LikeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Repository Service Provider.
 *
 * Registers all repository bindings.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    #[Override]
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        $this->app->bind(LikeRepositoryInterface::class, LikeRepository::class);
        $this->app->bind(FollowerRepositoryInterface::class, FollowerRepository::class);
        $this->app->bind(FeedRepositoryInterface::class, FeedRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
