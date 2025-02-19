<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6);
        $status = $this->faker->randomElement(['draft', 'published', 'archived']);

        return [
            'user_id' => User::factory(), // Creates a related user
            'title' => $title,
            'slug' => Str::slug($title) . '-' . rand(1000, 9999),
            'content' => $this->faker->paragraph(5),
            'excerpt' => $this->faker->sentence(10),
            'image' => 'https://via.placeholder.com/600x400.png',
            'status' => $status,
            'is_featured' => $this->faker->boolean(),
            'published_at' => $status === 'published' ? now() : null, // Set only if published
        ];
    }
}
