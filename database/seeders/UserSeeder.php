<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@knowledgehub.com',
            'username' => 'admin',
            'bio' => 'Administrador do Knowledge Hub',
            'avatar_url' => 'https://via.placeholder.com/200x200/4F46E5/FFFFFF?text=A',
        ]);

        User::factory()->author()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'bio' => 'Usuário de teste do sistema',
            'avatar_url' => 'https://via.placeholder.com/200x200/10B981/FFFFFF?text=T',
        ]);

        User::factory()->author()->create([
            'name' => 'Gabriel Silva',
            'email' => 'gabriel@knowledgehub.com',
            'username' => 'gabesilva',
            'bio' => 'Desenvolvedor Full Stack e escritor técnico',
            'avatar_url' => 'https://via.placeholder.com/200x200/F59E0B/FFFFFF?text=G',
        ]);

        User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@knowledgehub.com',
            'username' => 'mariasantos',
            'bio' => 'Moderadora da comunidade',
            'roles' => ['moderator', 'author', 'reader'],
            'avatar_url' => 'https://via.placeholder.com/200x200/EF4444/FFFFFF?text=M',
        ]);

        User::factory()->author()->count(5)->create();

        User::factory()->reader()->count(15)->create();

        User::factory()->count(10)->create();
    }
}
