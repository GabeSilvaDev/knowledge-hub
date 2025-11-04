<?php

namespace App\Traits;

use App\Models\ArticleVersion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

trait Versionable
{
    /**
     * Whether versioning is currently disabled.
     */
    protected bool $versioningDisabled = false;

    /**
     * Custom list of versionable fields (optional).
     *
     * @var array<int, string>|null
     */
    protected ?array $versionable = null;

    /**
     * Boot the versionable trait for a model.
     */
    public static function bootVersionable(): void
    {
        static::updating(function (self $model): void {
            if ($model->shouldCreateVersion()) {
                $model->createVersion();
            }
        });
    }

    /**
     * Get all versions of this article.
     *
     * @return HasMany<ArticleVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ArticleVersion::class, 'article_id')->orderBy('version_number', 'desc');
    }

    /**
     * Create a new version snapshot of the current article.
     */
    public function createVersion(?string $reason = null): ArticleVersion
    {
        $versionNumber = $this->getNextVersionNumber();
        $changedFields = $this->getChangedVersionableFields();

        $versionData = $this->getVersionableAttributes();
        $versionData['article_id'] = $this->_id;
        $versionData['version_number'] = $versionNumber;
        $versionData['versioned_by'] = Auth::id();
        $versionData['version_reason'] = $reason;
        $versionData['changed_fields'] = $changedFields;

        return ArticleVersion::create($versionData);
    }

    /**
     * Get the next version number for this article.
     */
    protected function getNextVersionNumber(): int
    {
        /** @var ArticleVersion|null $lastVersion */
        $lastVersion = $this->versions()->first();

        return $lastVersion !== null ? ($lastVersion->version_number + 1) : 1;
    }

    /**
     * Get the attributes that should be versioned.
     *
     * @return array<string, mixed>
     */
    protected function getVersionableAttributes(): array
    {
        $versionableFields = $this->getVersionableFields();

        /* @var array<string, mixed> */
        return collect($this->getOriginal())
            ->only($versionableFields)
            ->all();
    }

    /**
     * Get the list of fields that should be versioned.
     *
     * @return array<int, string>
     */
    protected function getVersionableFields(): array
    {
        return $this->versionable ?? [
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
    }

    /**
     * Get the fields that have changed and should trigger versioning.
     *
     * @return array<int, string>
     */
    protected function getChangedVersionableFields(): array
    {
        $versionableFields = $this->getVersionableFields();

        return collect($this->getDirty())
            ->keys()
            ->intersect(collect($versionableFields))
            ->values()
            ->all();
    }

    /**
     * Determine if a version should be created.
     */
    protected function shouldCreateVersion(): bool
    {
        return $this->versioningDisabled !== true;
    }

    /**
     * Restore the article to a specific version.
     */
    public function restoreToVersion(int $versionNumber): bool
    {
        $version = $this->versions()
            ->where('version_number', $versionNumber)
            ->first();

        if (! $version) {
            return false;
        }

        $this->disableVersioning();

        $restored = $this->update($version->only($this->getVersionableFields()));

        $this->enableVersioning();

        return $restored;
    }

    /**
     * Get a specific version of this article.
     */
    public function getVersion(int $versionNumber): ?ArticleVersion
    {
        return $this->versions()
            ->where('version_number', $versionNumber)
            ->first();
    }

    /**
     * Get the latest version of this article.
     */
    public function getLatestVersion(): ?ArticleVersion
    {
        return $this->versions()->first();
    }

    /**
     * Compare two versions and return the differences.
     *
     * @return array<string, array<string, mixed>>
     */
    public function compareVersions(int $versionA, int $versionB): array
    {
        $vA = $this->getVersion($versionA);
        $vB = $this->getVersion($versionB);

        if (! $vA || ! $vB) {
            return [];
        }

        $differences = [];
        $fields = $this->getVersionableFields();

        foreach ($fields as $field) {
            if ($vA->{$field} !== $vB->{$field}) {
                $differences[$field] = [
                    'version_' . $versionA => $vA->{$field},
                    'version_' . $versionB => $vB->{$field},
                ];
            }
        }

        return $differences;
    }

    /**
     * Temporarily disable versioning.
     *
     * @return $this
     */
    public function disableVersioning(): static
    {
        $this->versioningDisabled = true;

        return $this;
    }

    /**
     * Re-enable versioning.
     *
     * @return $this
     */
    public function enableVersioning(): static
    {
        $this->versioningDisabled = false;

        return $this;
    }

    /**
     * Get the total number of versions for this article.
     */
    public function getVersionCount(): int
    {
        return $this->versions()->count();
    }
}
