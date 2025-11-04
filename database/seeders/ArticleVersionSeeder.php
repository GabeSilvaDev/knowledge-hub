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

        Auth::login($user);

        foreach ($articles as $article) {
            $article->update(['title' => $article->title . ' - Editado']);
            $article->update(['content' => $article->content . "\n\nConteúdo atualizado."]);
        }

        Auth::logout();
    }
}
