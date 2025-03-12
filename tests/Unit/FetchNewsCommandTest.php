<?php
namespace Tests\Unit;

use App\Services\ArticleService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class FetchNewsCommandTest extends TestCase
{
    protected $articleService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the ArticleService
        $this->articleService = Mockery::mock(ArticleService::class);

        // Bind the mock ArticleService to the container
        $this->app->instance(ArticleService::class, $this->articleService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function test_that_it_fetches_articles_from_all_sources_successfully()
    {
        // Arrange
        $sources = ['guardian', 'nytimes'];
        $this->articleService
            ->shouldReceive('getNewsSources')
            ->once()
            ->andReturn($sources);

        foreach ($sources as $source) {
            $this->articleService
                ->shouldReceive('fetchAndStoreArticles')
                ->with($source)
                ->once();
        }

        // Act
        $exitCode = Artisan::call('app:fetch-news');

        // Assert
        $this->assertEquals(0, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('Starting to fetch articles from all sources', $output);
        $this->assertStringContainsString('Successfully fetched and stored articles from: guardian', $output);
        $this->assertStringContainsString('Successfully fetched and stored articles from: nytimes', $output);
    }


    public function test_that_it_handles_failures_when_fetching_articles()
    {
        // Arrange
        $sources = ['guardian', 'nytimes'];
        $this->articleService
            ->shouldReceive('getNewsSources')
            ->once()
            ->andReturn($sources);

        // Simulate a failure for the first source
        $this->articleService
            ->shouldReceive('fetchAndStoreArticles')
            ->with('guardian')
            ->once()
            ->andThrow(new \Exception('Failed to fetch articles'));

        // Simulate success for the second source
        $this->articleService
            ->shouldReceive('fetchAndStoreArticles')
            ->with('nytimes')
            ->once();

        // Act
        $exitCode = Artisan::call('app:fetch-news');

        // Assert
        $this->assertEquals(0, $exitCode); // Command should still exit with code 0 (handled failure)
        $output = Artisan::output();
        $this->assertStringContainsString('Failed fetching from guardian: Failed to fetch articles', $output);
        $this->assertStringContainsString('Successfully fetched and stored articles from: nytimes', $output);
    }


    public function test_that_it_handles_no_sources_available()
    {
        // Arrange
        $this->articleService
            ->shouldReceive('getNewsSources')
            ->once()
            ->andReturn([]);

        // Act
        $exitCode = Artisan::call('app:fetch-news');

        // Assert
        $this->assertEquals(0, $exitCode); // Command should exit with code 0 (no sources)
        $output = Artisan::output();
        $this->assertStringContainsString('Starting to fetch articles from all sources', $output);
        $this->assertStringContainsString('Finished fetching articles', $output);
    }
}
