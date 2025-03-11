<?php
namespace App\Services;

use App\Interfaces\NewsSourceInterface;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NytimesNewsService implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl;


    public function __construct()
    {
        $this->apiKey = config('services.nytimes.key');
        $this->baseUrl = config('services.nytimes.url');
    }

    /**
     */
    public function fetchArticles(): array
    {
        $allArticles = [];
        $page = 0;
        $maxResults = config('app.nyt_api_service_max');

        do {
            try {
                $response = Http::retry(3, 100)->get($this->baseUrl, [
                    'api-key' => $this->apiKey,
                    'page' => $page,
                ])->json();

                if (empty($response['response']['docs'])) {
                    Log::warning("[NytimesNewsService] NYT API: No articles found on page {$page}");
                    break;
                }

                $articles = $this->normalizeArticles($response['response']['docs']);
                $allArticles = array_merge($allArticles, $articles);

                $page++;

            } catch (\Exception $e) {
                Log::error("[NytimesNewsService] Error fetching page {$page}: {$e->getMessage()}");
                $page++;
            }
        } while ($page < $maxResults);

        return $allArticles;
    }






    public function normalizeArticles(array $articles): array
    {
        $newsSourcesMapping = (new ArticleService())->getNewsSourcesMapping();

        $source = array_search(static::class, $newsSourcesMapping, true);

        return array_map(function ($article) use ($source) {
            return [
                'unique_id' => $article['_id'] ?? $article['web_url'] ?? null,
                'title' => $title = $article['abstract'] ?? $article['snippet'] ??  $article['headline']['main'] ?? null,
                'slug' => slugify($title),
                'content' => $article['lead_paragraph'] ?? $title ?? null,
                'url' => $article['web_url'] ?? $article['uri'] ?? null,
                'source' => $source,
                'category' => $article['section_name'] ?? $article['subsection_name'] ?? $article['news_desk'] ?? "General",
                'contributor' =>  $this->cleanContributorName($article['byline']['original'] ?? null),
                'published_at' => isset($article['pub_date'])
                    ? Carbon::parse($article['pub_date'])->format('Y-m-d H:i:s')
                    : now(),
            ];
        }, $articles);
    }

    private function cleanContributorName(?string $name): ?string
    {
        if ($name && stripos($name, 'By ') === 0) {
            return trim(substr($name, 3));
        }
        return $name;
    }
}
