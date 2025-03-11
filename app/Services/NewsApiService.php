<?php
namespace App\Services;

use App\Interfaces\NewsSourceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

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
     * @throws ConnectionException
     */
    public function fetchArticles(): array
    {
        $allArticles = [];
        $page = 1;
        $pageSize = config('app.api_news_page_size');
        $maxResults = config('app.news_api_service_max');

        do {
            $response = Http::retry(3, 100)
                ->get($this->baseUrl, [
                    'apiKey' => $this->apiKey,
                    'q' => 'technology',
                    'sortBy' => 'publishedAt',
                    'to' => date('Y-m-d'),
                    'pageSize' => config('app.api_news_page_size'),
                    'page' => $page,
                ])->json();

            $articles = $response['articles'] ?? [];
            $allArticles = array_merge($allArticles, $this->normalizeArticles($articles));

            $currentPage = $page;
            $totalPages = min(ceil($response['totalResults'] / $pageSize), ceil($maxResults / $pageSize));

            $page++;
        } while ($currentPage < $totalPages && count($allArticles) < $maxResults);
        return $allArticles;
    }


    public function normalizeArticles(array $articles): array
    {
        $source = array_search(static::class, app('news.sources'), true);



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
