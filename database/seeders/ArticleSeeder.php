<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('roles', 'like', '%author%')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No authors found. Please run UserSeeder first.');

            return;
        }

        $mainAuthors = User::whereIn('username', ['admin', 'gabesilva'])->get();

        foreach ($mainAuthors as $author) {
            Article::factory()
                ->count(3)
                ->featured()
                ->published()
                ->state(['author_id' => $author->_id])
                ->create();
        }

        Article::factory()
            ->count(15)
            ->ofType(ArticleType::WIKI->value)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(12)
            ->ofType(ArticleType::TUTORIAL->value)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(8)
            ->ofType(ArticleType::NEWS->value)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(20)
            ->ofType(ArticleType::ARTICLE->value)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(15)
            ->ofType(ArticleType::POST->value)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(10)
            ->draft()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(5)
            ->state([
                'status' => ArticleStatus::PRIVATE->value,
                'author_id' => fn () => $users->random()->_id,
            ])
            ->create();
    }
}
