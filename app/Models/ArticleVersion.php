<?php

namespace App\Models;

use Database\Factories\ArticleVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use MongoDB\Laravel\Eloquent\Model;

/**
 * @use HasFactory<ArticleVersionFactory>
 *
 * @property string $article_id
 * @property int $version_number
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $excerpt
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
 * @property string|null $versioned_by
 * @property string|null $version_reason
 * @property array<int, string> $changed_fields
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ArticleVersion extends Model
{
    /** @use HasFactory<ArticleVersionFactory> */
    use HasFactory;

    protected $connection = 'mongodb';

    /** @var string */
    protected $collection = 'article_versions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'version_number',
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
        'versioned_by',
        'version_reason',
        'changed_fields',
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
            'changed_fields' => 'array',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'reading_time' => 'integer',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'datetime',
            'version_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the article that owns this version.
     *
     * @return BelongsTo<Article, ArticleVersion>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    /**
     * Get the author of this version.
     *
     * @return BelongsTo<User, ArticleVersion>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the user who created this version.
     *
     * @return BelongsTo<User, ArticleVersion>
     */
    public function versionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'versioned_by');
    }
}
