<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Exceptions\ArticleRefreshException;
use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private readonly Article $model
    ) {}

    /**
     * @return QueryBuilder<Article>
     */
    public function query(): QueryBuilder
    {
        return QueryBuilder::for(Article::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('author_id'),
                AllowedFilter::exact('is_featured'),
                AllowedFilter::exact('is_pinned'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('content'),
                AllowedFilter::partial('excerpt'),
                AllowedFilter::scope('tags'),
                AllowedFilter::scope('categories'),
            ])
            ->allowedSorts([
                'title',
                'created_at',
                'updated_at',
                'published_at',
                'view_count',
                'like_count',
                'reading_time',
            ])
            ->allowedIncludes(['author'])
            ->defaultSort('-created_at');
    }

    public function create(CreateArticleDTO $dto): Article
    {
        return $this->model->create($dto->toArray());
    }

    public function update(Article $article, array $data): Article
    {
        $article->update($data);

        $freshArticle = $article->fresh();

        if ($freshArticle === null) {
            throw ArticleRefreshException::failedToRefresh();
        }

        return $freshArticle;
    }

    public function delete(Article $article): bool
    {
        return (bool) $article->delete();
    }

    /**
     * Get popular articles based on view count.
     *
     * @return Collection<int, Article>
     */
    public function getPopularArticles(int $limit = 10, int $days = 30): Collection
    {
        $startDate = now()->subDays($days);

        return Article::query()
            ->where('status', 'published')
            ->where('published_at', '>=', $startDate)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
