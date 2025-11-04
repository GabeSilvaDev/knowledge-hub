<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleVersion>
 */
class ArticleVersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ArticleVersion>
     */
    protected $model = ArticleVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'version_number' => $this->faker->numberBetween(1, 10),
            'title' => $this->faker->sentence(6, true),
            'slug' => $this->faker->slug(),
            'content' => is_array($this->faker->paragraphs(8, true))
                ? implode("\n\n", $this->faker->paragraphs(8, true))
                : $this->faker->paragraphs(8, true),
            'excerpt' => $this->faker->text(200),
            'author_id' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'published', 'private']),
            'type' => $this->faker->randomElement(['article', 'post', 'wiki', 'tutorial', 'news']),
            'featured_image' => $this->faker->imageUrl(800, 600, 'technology'),
            'tags' => $this->faker->randomElements(['PHP', 'Laravel', 'MongoDB', 'JavaScript', 'Vue.js'], 3),
            'categories' => $this->faker->randomElements(['Tecnologia', 'Programação', 'Tutorial'], 2),
            'meta_data' => [
                'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
                'estimated_time' => $this->faker->numberBetween(5, 60),
            ],
            'view_count' => $this->faker->numberBetween(0, 1000),
            'like_count' => $this->faker->numberBetween(0, 100),
            'comment_count' => $this->faker->numberBetween(0, 50),
            'reading_time' => $this->faker->numberBetween(2, 15),
            'is_featured' => $this->faker->boolean(20),
            'is_pinned' => $this->faker->boolean(10),
            'published_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'seo_title' => $this->faker->sentence(6),
            'seo_description' => $this->faker->sentence(20),
            'seo_keywords' => implode(', ', (array) $this->faker->words(5)),
            'versioned_by' => User::factory(),
            'version_reason' => $this->faker->optional(0.7)->randomElement([
                'Correção de erros de digitação',
                'Atualização de conteúdo',
                'Melhorias no texto',
                'Adição de novas informações',
                'Revisão geral',
                'Correção de links quebrados',
            ]),
            'changed_fields' => $this->faker->randomElements([
                'title',
                'content',
                'excerpt',
                'tags',
                'categories',
                'featured_image',
            ], $this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * State for a specific version number.
     */
    public function versionNumber(int $number): static
    {
        return $this->state(fn (array $attributes): array => [
            'version_number' => $number,
        ]);
    }

    /**
     * State for a version with specific changed fields.
     *
     * @param  array<string>  $fields
     */
    public function changedFields(array $fields): static
    {
        return $this->state(fn (array $attributes): array => [
            'changed_fields' => $fields,
        ]);
    }
}
