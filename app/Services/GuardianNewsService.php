<?php
namespace App\Services;

use App\Interfaces\NewsSourceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GuardianNewsService implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
        $this->baseUrl = config('services.guardian.url');
    }

    /**
     * @throws ConnectionException
     */
    public function fetchArticles(): array
    {
        $allArticles = [];
        $page = 1;

        do {
            $response = Http::retry(3, 100)
                ->get($this->baseUrl, [
                    'api-key' => $this->apiKey,
                    'order-by' => 'newest',
                    'from-date' => date('Y-m-d'),
                    'show-fields' => 'body,body,shortUrl',
                    'show-tags' => 'contributor',
                    'page-size' => config('app.api_news_page_size'),
                    'page' => $page,
                ])->json();

            $articles = $response['response']['results'] ?? [];
            $allArticles = array_merge($allArticles, $this->normalizeArticles($articles));

            $currentPage = $response['response']['currentPage'] ?? $page;
            $totalPages = $response['response']['pages'] ?? 1;

            $page++;
        } while ($currentPage < $totalPages);

        return $allArticles;
    }


    public function normalizeArticles(array $articles): array
    {
        $source = array_search(static::class, app('news.sources'), true);

        return array_map(function ($article) use ($source) {
            return [
                'unique_id' => $article['id'],
                'title' => $title = $article['webTitle'] ?? null,
                'slug' => slugify($title),
                'content' => $article['fields']['body'] ?? '',
                'url' => $article['webUrl'] ?? null,
                'source' => $source,
                'category' => $article['sectionName'] ?? $article['pillarName'] ?? "General",
                'contributor' => $article['tags'][0]['webTitle'] ?? null,
                'published_at' => isset($article['webPublicationDate'])
                    ? Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s')
                    : now(),
            ];
        }, $articles);
    }
}
