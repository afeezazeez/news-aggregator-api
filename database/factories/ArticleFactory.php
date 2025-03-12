<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unique_id' => $this->faker->uuid,
            'title' => $title = $this->faker->sentence,
            'url' => $this->faker->url,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraphs(3, true),
            'source' => $this->faker->randomElement(['guardian', 'newsapi', 'nytimes']),
            'category' => $this->faker->randomElement(['Arts', 'Business', 'Technology', 'Environment', 'Health']),
            'contributor' => $this->faker->name,
            'published_at' => now(),
        ];
    }
}
