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
        ]);

        User::factory()->author()->create([
            'name' => 'Gabriel Silva',
            'email' => 'gabriel@knowledgehub.com',
            'username' => 'gabesilva',
        ]);

        User::factory()->author()->count(10)->create();

        User::factory()->reader()->count(20)->create();

        User::factory()->count(10)->create();
    }
}
