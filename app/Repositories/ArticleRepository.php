<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\DTOs\CreateArticleDTO;
use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private readonly Article $model
    ) {}

    public function findById(string $id): ?Article
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Article
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function create(CreateArticleDTO $dto): Article
    {
        return $this->model->create($dto->toArray());
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getPublished(): Collection
    {
        return $this->model
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->model
            ->where('is_featured', true)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByAuthor(string $authorId): Collection
    {
        return $this->model
            ->where('author_id', $authorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function search(string $term): Collection
    {
        return $this->model
            ->where('title', 'like', "%{$term}%")
            ->orWhere('content', 'like', "%{$term}%")
            ->orWhere('excerpt', 'like', "%{$term}%")
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByTags(array $tags): Collection
    {
        $query = $this->model->newQuery();

        foreach ($tags as $tag) {
            $query->orWhere('tags', 'like', "%{$tag}%");
        }

        return $query
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
