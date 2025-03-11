<?php
namespace App\Services;

use App\Interfaces\NewsSourceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl;


    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
        $this->baseUrl = config('services.newsapi.url');
    }

    /**
     */
    public function fetchArticles(): array
    {
        $allArticles = [];
        $page = 1;
        $pageSize = config('app.news_page_size');
        $maxResults = config('app.news_api_service_max');

        do {
            try {
                $response = Http::retry(3, 100)
                    ->get($this->baseUrl, [
                        'apiKey' => $this->apiKey,
                        'q' => 'technology',
                        'sortBy' => 'publishedAt',
                        'to' => date('Y-m-d'),
                        'pageSize' => $pageSize,
                        'page' => $page,
                    ])->json();

                if (empty($response['articles'])) {
                    Log::warning("[NewsApiService] No articles found on page {$page}");
                    break;
                }

                $articles = $this->normalizeArticles($response['articles']);
                $allArticles = array_merge($allArticles, $articles);


                $currentPage = $page;
                $totalPages = min(
                    ceil(($response['totalResults'] ?? 0) / $pageSize),
                    ceil($maxResults / $pageSize)
                );

                $page++;

            } catch (\Exception $e) {
                Log::error("[NewsApiService]  Error fetching page {$page}: {$e->getMessage()}");
                $page++;
            }
        } while ($currentPage < $totalPages && count($allArticles) < $maxResults);

        return $allArticles;
    }



    public function normalizeArticles(array $articles): array
    {
       $newsSourcesMapping = (new ArticleService())->getNewsSourcesMapping();

       $source = array_search(static::class, $newsSourcesMapping, true);

        return array_map(function ($article) use ($source) {
            return [
                'unique_id' => $article['url'] ?? null,
                'title' => $title = $article['title'] ?? null,
                'slug' => slugify($title),
                'content' => $article['content'] ?? null,
                'url' => $article['url'] ?? null,
                'source' => $source,
                'category' => "General",
                'contributor' => $article['author'] ?? null,
                'published_at' => isset($article['publishedAt'])
                    ? Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s')
                    : now(),
            ];
        }, $articles);
    }
}
