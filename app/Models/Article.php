<?php

namespace App\Models;

use App\Traits\Versionable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * Article Model.
 *
 * Represents a content article in the system with versioning, soft deletes,
 * and comprehensive metadata including SEO fields, tags, and categories.
 *
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

    use Searchable;
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
     * Defines the relationship to the User who created this article.
     *
     * @return BelongsTo<User, Article> The author relationship
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the comments for this article.
     *
     * @return HasMany<Comment, Article>
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    /**
     * Get the likes for this article.
     *
     * @return HasMany<Like, Article>
     */
    public function likes()
    {
        return $this->hasMany(Like::class, 'article_id');
    }

    /**
     * Scope a query to filter articles by tags.
     *
     * Filters articles that contain any of the specified tags.
     * Tags can be provided as a comma-separated string or array.
     *
     * @param  Builder<Article>  $query  The query builder instance
     * @param  string|array<int, string>  $tags  Tags to filter by
     * @return Builder<Article> The filtered query builder
     */
    public function scopeTags(Builder $query, string|array $tags): Builder
    {
        $tagsArray = is_array($tags) ? $tags : explode(',', $tags);
        $tagsArray = array_map('trim', $tagsArray);

        return $query->where(function (Builder $q) use ($tagsArray): void {
            foreach ($tagsArray as $tag) {
                $q->orWhere('tags', $tag);
            }
        });
    }

    /**
     * Scope a query to filter articles by categories.
     *
     * Filters articles that belong to any of the specified categories.
     * Categories can be provided as a comma-separated string or array.
     *
     * @param  Builder<Article>  $query  The query builder instance
     * @param  string|array<int, string>  $categories  Categories to filter by
     * @return Builder<Article> The filtered query builder
     */
    public function scopeCategories(Builder $query, string|array $categories): Builder
    {
        $categoriesArray = is_array($categories) ? $categories : explode(',', $categories);
        $categoriesArray = array_map('trim', $categoriesArray);

        return $query->where(function (Builder $q) use ($categoriesArray): void {
            foreach ($categoriesArray as $category) {
                $q->orWhere('categories', $category);
            }
        });
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'author_id' => $this->author_id,
            'status' => $this->status,
            'type' => $this->type,
            'tags' => $this->tags,
            'categories' => $this->categories,
            'published_at' => $this->published_at?->timestamp,
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    /**
     * Get the value used to index the model.
     */
    public function getScoutKey(): string
    {
        $id = $this->id;

        if (is_string($id)) {
            return $id;
        }

        return '';
    }

    /**
     * Get the key name used to index the model.
     */
    public function getScoutKeyName(): string
    {
        return 'id';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return ! $this->trashed();
    }
}
