<?php

namespace Database\Seeders;

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

        Article::factory()
            ->count(20)
            ->published()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();

        Article::factory()
            ->count(10)
            ->draft()
            ->state(fn (): array => ['author_id' => $users->random()->_id])
            ->create();
    }
}
