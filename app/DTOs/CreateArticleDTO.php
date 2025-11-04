<?php

namespace App\DTOs;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\ValueObjects\ArticleContent;
use App\ValueObjects\ArticleMetadata;
use App\ValueObjects\ArticleSEO;
use App\ValueObjects\Content;
use App\ValueObjects\Slug;
use App\ValueObjects\Title;
use App\ValueObjects\Url;
use App\ValueObjects\UserId;
use DateTime;

class CreateArticleDTO
{
    public function __construct(
        public readonly ArticleContent $content,
        public readonly UserId $author_id,
        public readonly ArticleMetadata $metadata,
        public readonly ?Url $featured_image = null,
        /** @var array<string> */
        public readonly array $tags = [],
        /** @var array<string> */
        public readonly array $categories = [],
        /** @var array<string, mixed> */
        public readonly array $meta_data = [],
        public readonly ?ArticleSEO $seo = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->content->getTitle()->getValue(),
            'slug' => $this->content->getGeneratedSlug()->getValue(),
            'content' => $this->content->getContent()->getValue(),
            'excerpt' => $this->content->getExcerpt(),
            'author_id' => $this->author_id->getValue(),
            'status' => $this->metadata->getStatus()->value,
            'type' => $this->metadata->getType()->value,
            'featured_image' => $this->featured_image?->getValue(),
            'tags' => $this->tags,
            'categories' => $this->categories,
            'meta_data' => $this->meta_data,
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'reading_time' => $this->content->getReadingTime(),
            'is_featured' => $this->metadata->isFeatured(),
            'is_pinned' => $this->metadata->isPinned(),
            'published_at' => $this->metadata->getPublishedAt(),
            'seo_title' => $this->seo?->getSeoTitle()?->getValue(),
            'seo_description' => $this->seo?->getSeoDescription(),
            'seo_keywords' => $this->seo?->getSeoKeywords(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $validatedData = self::validateAndExtractData($data);

        $seoTitleObject = $validatedData['seoTitle'] !== null ? Title::from($validatedData['seoTitle']) : null;
        $hasSeo = $seoTitleObject instanceof Title || $validatedData['seoDescription'] !== null || $validatedData['seoKeywords'] !== null;
        $seo = $hasSeo
            ? ArticleSEO::create($seoTitleObject, $validatedData['seoDescription'], $validatedData['seoKeywords'])
            : null;

        return new self(
            content: ArticleContent::create(
                title: Title::from($validatedData['title']),
                content: Content::from($validatedData['content']),
                slug: $validatedData['slug'] !== null ? Slug::from($validatedData['slug']) : null
            ),
            author_id: UserId::from($validatedData['authorId']),
            metadata: ArticleMetadata::create(
                status: ArticleStatus::from($validatedData['status']),
                type: ArticleType::from($validatedData['type']),
                is_featured: $validatedData['isFeatured'],
                is_pinned: $validatedData['isPinned'],
                published_at: $validatedData['publishedAt'] !== null ? new DateTime($validatedData['publishedAt']) : null
            ),
            featured_image: $validatedData['featuredImage'] !== null ? Url::from($validatedData['featuredImage']) : null,
            tags: $validatedData['tags'],
            categories: $validatedData['categories'],
            meta_data: $validatedData['metaData'],
            seo: $seo,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     title: string,
     *     content: string,
     *     slug: string|null,
     *     authorId: string,
     *     seoTitle: string|null,
     *     seoDescription: string|null,
     *     seoKeywords: string|null,
     *     featuredImage: string|null,
     *     publishedAt: string|null,
     *     isFeatured: bool,
     *     isPinned: bool,
     *     status: int|string,
     *     type: int|string,
     *     tags: array<string>,
     *     categories: array<string>,
     *     metaData: array<string, mixed>
     * }
     */
    private static function validateAndExtractData(array $data): array
    {
        self::validateRequiredStringFields($data);
        self::validateOptionalStringFields($data);
        self::validateBooleanFields($data);
        self::validateEnumFields($data);

        $title = $data['title'];
        $content = $data['content'];
        $authorId = $data['author_id'];

        assert(is_string($title));
        assert(is_string($content));
        assert(is_string($authorId));

        $slug = $data['slug'] ?? null;
        $seoTitle = $data['seo_title'] ?? null;
        $seoDescription = $data['seo_description'] ?? null;
        $seoKeywords = $data['seo_keywords'] ?? null;
        $featuredImage = $data['featured_image'] ?? null;
        $publishedAt = $data['published_at'] ?? null;

        assert($slug === null || is_string($slug));
        assert($seoTitle === null || is_string($seoTitle));
        assert($seoDescription === null || is_string($seoDescription));
        assert($seoKeywords === null || is_string($seoKeywords));
        assert($featuredImage === null || is_string($featuredImage));
        assert($publishedAt === null || is_string($publishedAt));

        $isFeatured = $data['is_featured'] ?? false;
        $isPinned = $data['is_pinned'] ?? false;

        assert(is_bool($isFeatured));
        assert(is_bool($isPinned));

        $status = $data['status'] ?? ArticleStatus::DRAFT->value;
        $type = $data['type'] ?? ArticleType::ARTICLE->value;

        assert(is_string($status) || is_int($status));
        assert(is_string($type) || is_int($type));

        $metaData = $data['meta_data'] ?? [];

        if (! is_array($metaData)) {
            $metaData = [];
        }

        /** @var array<string, mixed> $validatedMetaData */
        $validatedMetaData = [];
        foreach ($metaData as $key => $value) {
            if (is_string($key)) {
                $validatedMetaData[$key] = $value;
            }
        }

        return [
            'title' => $title,
            'content' => $content,
            'slug' => $slug,
            'authorId' => $authorId,
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'seoKeywords' => $seoKeywords,
            'featuredImage' => $featuredImage,
            'publishedAt' => $publishedAt,
            'isFeatured' => $isFeatured,
            'isPinned' => $isPinned,
            'status' => $status,
            'type' => $type,
            'tags' => self::validateStringArray($data['tags'] ?? []),
            'categories' => self::validateStringArray($data['categories'] ?? []),
            'metaData' => $validatedMetaData,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function validateRequiredStringFields(array $data): void
    {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';
        $authorId = $data['author_id'] ?? '';

        if (! is_string($title) || ! is_string($content) || ! is_string($authorId)) {
            throw new \InvalidArgumentException('Title, content and author_id must be strings');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function validateOptionalStringFields(array $data): void
    {
        $fields = ['slug', 'seo_title', 'seo_description', 'seo_keywords', 'featured_image', 'published_at'];

        foreach ($fields as $field) {
            $value = $data[$field] ?? null;
            if ($value !== null && ! is_string($value)) {
                throw new \InvalidArgumentException("Field {$field} must be a string or null");
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function validateBooleanFields(array $data): void
    {
        $isFeatured = $data['is_featured'] ?? false;
        $isPinned = $data['is_pinned'] ?? false;

        if (! is_bool($isFeatured) || ! is_bool($isPinned)) {
            throw new \InvalidArgumentException('is_featured and is_pinned must be booleans');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function validateEnumFields(array $data): void
    {
        $status = $data['status'] ?? ArticleStatus::DRAFT->value;
        $type = $data['type'] ?? ArticleType::ARTICLE->value;

        if ((! is_string($status) && ! is_int($status)) || (! is_string($type) && ! is_int($type))) {
            throw new \InvalidArgumentException('Status and type must be strings or ints');
        }
    }

    /**
     * @return array<string>
     */
    private static function validateStringArray(mixed $array): array
    {
        if (! is_array($array)) {
            return [];
        }

        $result = [];
        foreach ($array as $item) {
            if (is_string($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
