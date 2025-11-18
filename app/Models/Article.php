<?php

namespace App\Models;

use App\Traits\Versionable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $excerpt
 * @property string $author_id
 * @property string $status
 * @property string $type
 * @property string|null $featured_image
 * @property array<int, string> $tags
 * @property array<int, string> $categories
 * @property array<string, mixed> $meta_data
 * @property int $view_count
 * @property int $like_count
 * @property int $comment_count
 * @property int $reading_time
 * @property bool $is_featured
 * @property bool $is_pinned
 * @property Carbon|null $published_at
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $seo_keywords
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @use HasFactory<ArticleFactory>
 */
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use SoftDeletes;
    use Versionable;

    protected $connection = 'mongodb';

    /** @var string */
    protected $collection = 'articles';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'author_id',
        'status',
        'type',
        'featured_image',
        'tags',
        'categories',
        'meta_data',
        'view_count',
        'like_count',
        'comment_count',
        'reading_time',
        'is_featured',
        'is_pinned',
        'published_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'categories' => 'array',
            'meta_data' => 'array',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'reading_time' => 'integer',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the author of the article.
     *
     * @return BelongsTo<User, Article>
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope a query to filter articles by tags.
     *
     * @param  Builder<Article>  $query
     * @param  string|array<int, string>  $tags
     * @return Builder<Article>
     */
    public function scopeTags(Builder $query, string|array $tags): Builder
    {
        $tagsArray = is_array($tags) ? $tags : explode(',', $tags);

        return $query->where(function (Builder $q) use ($tagsArray): void {
            foreach ($tagsArray as $tag) {
                $q->orWhere('tags', 'like', '%' . trim($tag) . '%');
            }
        });
    }

    /**
     * Scope a query to filter articles by categories.
     *
     * @param  Builder<Article>  $query
     * @param  string|array<int, string>  $categories
     * @return Builder<Article>
     */
    public function scopeCategories(Builder $query, string|array $categories): Builder
    {
        $categoriesArray = is_array($categories) ? $categories : explode(',', $categories);

        return $query->where(function (Builder $q) use ($categoriesArray): void {
            foreach ($categoriesArray as $category) {
                $q->orWhere('categories', 'like', '%' . trim($category) . '%');
            }
        });
    }
}
