<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Support\Str;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_it_returns_paginated_articles_with_correct_meta(): void
    {
        // Seed 15 articles.
        Article::factory()->count(15)->create();

        // Request page 2 with 5 articles per page.
        $response = $this->getJson('/api/articles?perPage=5&page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success',
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['current_page']);
        $this->assertEquals(5, (int)$data['per_page']);
        $this->assertEquals(15, $data['total']);
    }


    public function test_that_it_applies_filter_query_parameters(): void
    {
        Article::factory()->create([
            'category' => 'Business',
            'source'   => 'guardian',
        ]);
        Article::factory()->create([
            'category' => 'Technology',
            'source'   => 'newsapi',
        ]);

        $response = $this->getJson('/api/articles?categories=Business');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data['data'] as $article) {
            $this->assertEquals('Business', $article['category']);
        }
    }

    public function test_that_it_applies_multiple_filter_query_parameters(): void
    {
        Article::factory()->create([
            'category'    => 'Business',
            'source'      => 'guardian',
            'contributor' => 'John Doe',
        ]);

        Article::factory()->create([
            'category'    => 'Technology',
            'source'      => 'newsapi',
            'contributor' => 'Jane Smith',
        ]);

        Article::factory()->create([
            'category'    => 'Arts',
            'source'      => 'nytimes',
            'contributor' => 'Another Author',
        ]);

        // Call the endpoint with multiple filter parameters (comma-separated categories)
        $response = $this->getJson('/api/articles?categories=Business,Technology');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'source',
                            'slug',
                            'title',
                            'url',
                            'category',
                            'contributor',
                            'published_at',
                        ]
                    ],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success'
            ]);

        // Assert that every returned article has a category that is either Business or Technology.
        $data = $response->json('data');
        foreach ($data['data'] as $article) {
            $this->assertContains(
                $article['category'],
                ['Business', 'Technology'],
                "Article category must be either Business or Technology."
            );
        }
    }

    public function test_that_it_returns_single_article_by_slug(): void
    {
        $article = Article::factory()->create([
            'source' => 'guardian',
            'slug'   => 'australia-news-live-pm-says-trump-tariffs-not',
            'title'  => 'Australia news live: PM says Trump tariffs not a friendly act',
        ]);

        $response = $this->getJson('/api/articles/' . $article->slug);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'source',
                    'slug',
                    'title',
                    'url',
                    'category',
                    'contributor',
                    'published_at',
                    'content',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'slug'   => 'australia-news-live-pm-says-trump-tariffs-not',
                    'source' => 'guardian',
                ]
            ]);
    }

    public function it_returns_error_for_invalid_slug(): void
    {
        $response = $this->getJson('/api/articles/invalid-slug');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found'
            ]);
    }

}
