<?php
namespace App\Services;

use App\Interfaces\NewsSourceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Fetches articles from the external news API.
     *
     * This method retrieves articles in a paginated manner, making multiple requests
     * until all pages are fetched. It retries failed requests up to 3 times before logging errors.
     *
     * @return array List of normalized articles.
     */
    public function fetchArticles(): array
    {
        $allArticles = [];
        $page = 1;
        $pageSize = config('app.api_news_page_size');

        do {
            try {
                $response = Http::retry(3, 100)
                    ->get($this->baseUrl, [
                        'api-key' => $this->apiKey,
                        'order-by' => 'newest',
                        'from-date' => date('Y-m-d'),
                        'show-fields' => 'body,body,shortUrl',
                        'show-tags' => 'contributor',
                        'page-size' => $pageSize,
                        'page' => $page,
                    ])->json();

                if (empty($response['response']['results'])) {
                    Log::warning("[GuardianNewsService] No articles found on page {$page}");
                    break;
                }

                $articles = $this->normalizeArticles($response['response']['results']);
                $allArticles = array_merge($allArticles, $articles);

                $currentPage = $response['response']['currentPage'] ?? $page;
                $totalPages = $response['response']['pages'] ?? 1;
                $page++;

            } catch (\Exception $e) {
                Log::error("[GuardianNewsService] Error fetching page {$page}: {$e->getMessage()}");
                $page++;
            }
        } while ($currentPage < $totalPages);

        return $allArticles;
    }



    /**
     * Normalizes articles retrieved from the external news API.
     *
     * This method processes raw API responses, mapping relevant fields to a structured format
     * and assigning default values where necessary.
     *
     * @param array $articles List of articles fetched from the API.
     * @return array Normalized articles with standardized attributes.
     */
    public function normalizeArticles(array $articles): array
    {
        $newsSourcesMapping = (new ArticleService())->getNewsSourcesMapping();

        $source = array_search(static::class, $newsSourcesMapping, true);

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
