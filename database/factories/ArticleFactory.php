<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6, true);
        $content = $this->faker->paragraphs(8, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $content,
            'excerpt' => Str::limit(strip_tags($content), 200),
            'author_id' => User::factory(),
            'status' => $this->faker->randomElement([
                ArticleStatus::DRAFT->value,
                ArticleStatus::PUBLISHED->value,
                ArticleStatus::PRIVATE->value,
            ]),
            'type' => $this->faker->randomElement([
                ArticleType::ARTICLE->value,
                ArticleType::POST->value,
                ArticleType::WIKI->value,
                ArticleType::TUTORIAL->value,
                ArticleType::NEWS->value,
            ]),
            'featured_image' => $this->faker->imageUrl(800, 600, 'technology'),
            'tags' => $this->faker->randomElements([
                'PHP', 'Laravel', 'MongoDB', 'JavaScript', 'Vue.js', 'React',
                'Python', 'Docker', 'AWS', 'DevOps', 'Machine Learning',
                'Data Science', 'Frontend', 'Backend', 'Full Stack',
            ], random_int(2, 5)),
            'categories' => $this->faker->randomElements([
                'Tecnologia', 'Programação', 'Tutorial', 'Notícias',
                'Análise', 'Opinião', 'Review', 'Guia',
            ], random_int(1, 3)),
            'meta_data' => [
                'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
                'estimated_time' => $this->faker->numberBetween(5, 60),
                'source' => $this->faker->url(),
            ],
            'view_count' => $this->faker->numberBetween(0, 10000),
            'like_count' => $this->faker->numberBetween(0, 500),
            'comment_count' => $this->faker->numberBetween(0, 100),
            'reading_time' => $this->faker->numberBetween(2, 15),
            'is_featured' => $this->faker->boolean(20),
            'is_pinned' => $this->faker->boolean(10),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'seo_title' => $title,
            'seo_description' => $this->faker->sentence(20),
            'seo_keywords' => implode(', ', $this->faker->words(10)),
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::PUBLISHED->value,
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that the article is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ArticleStatus::DRAFT->value,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the article is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the article is of a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }
}
