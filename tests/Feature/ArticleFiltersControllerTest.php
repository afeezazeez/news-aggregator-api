<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArticleFiltersControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_it_returns_filter_options_based_on_articles()
    {
        // Seed articles
        Article::factory()->count(10)->create([
            'source' => 'guardian',
            'category' => 'Business',
            'contributor' => 'John Doe',
        ]);

        Article::factory()->count(5)->create([
            'source' => 'newsapi',
            'category' => 'Technology',
            'contributor' => 'Jane Smith',
        ]);

        $response = $this->getJson('/api/filters');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'categories' => ['Business', 'Technology'],
                    'sources' => ['guardian', 'newsapi'],
                    'authors' => ['John Doe', 'Jane Smith'],
                ],
                'message' => 'Success'
            ])->assertJsonStructure([
                'success',
                'data' => [
                    'categories',
                    'sources',
                    'authors',
                ],
                'message'
            ]);
    }

    public function test_that_it_returns_empty_filter_options_when_no_articles_exist()
    {
        $response = $this->getJson('/api/filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'categories',
                    'sources',
                    'authors',
                ],
                'message'
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'categories' => [],
                    'sources'    => [],
                    'authors'    => [],
                ],
                'message' => 'Success'
            ]);
    }

    public function test_that_it_caches_filter_options(): void
    {

        Article::factory()->create([
            'source'      => 'guardian',
            'category'    => 'Business',
            'contributor' => 'John Doe',
        ]);
        Article::factory()->create([
            'source'      => 'newsapi',
            'category'    => 'Technology',
            'contributor' => 'Jane Smith',
        ]);

        $response = $this->getJson('/api/filters');
        $response->assertStatus(200);

        $responseData = $response->json('data');
        $cacheFilters = Cache::get('article_filters');

        $this->assertEquals($cacheFilters, $responseData);
    }

}
