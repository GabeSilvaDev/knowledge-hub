<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends UserFactory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->name();
        $username = $this->faker->unique()->userName();

        if (static::$password === null) {
            static::$password = Hash::make('password');
        }

        return [
            'name' => $name,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password,
            'username' => $username,
            'avatar_url' => $this->faker->optional(0.7)->imageUrl(200, 200, 'people'),
            'bio' => $this->faker->optional(0.6)->sentence(10),
            'roles' => $this->faker->randomElements(['reader', 'author', 'moderator'], $this->faker->numberBetween(1, 2)),
            'last_login_at' => $this->faker->optional(0.8)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'roles' => ['admin', 'author', 'reader'],
        ]);
    }

    /**
     * Indicate that the user is an author.
     */
    public function author(): static
    {
        return $this->state(fn (array $attributes): array => [
            'roles' => ['author', 'reader'],
        ]);
    }

    /**
     * Indicate that the user is just a reader.
     */
    public function reader(): static
    {
        return $this->state(fn (array $attributes): array => [
            'roles' => ['reader'],
        ]);
    }
}
