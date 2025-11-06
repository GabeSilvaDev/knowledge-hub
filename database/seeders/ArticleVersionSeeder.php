<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ArticleVersionSeeder extends Seeder
{
    public function run(): void
    {
        $articles = Article::limit(10)->get();
        $user = User::first();

        if (! $user) {
            return;
        }

        Auth::login($user);

        foreach ($articles as $article) {
            $title = $article->getAttribute('title');
            if (is_string($title)) {
                $article->update(['title' => $title . ' - Editado']);
            }

            $content = $article->getAttribute('content');
            if (is_string($content)) {
                $article->update(['content' => $content . "\n\nConteúdo atualizado."]);
            }
        }

        Auth::logout();
    }
}
