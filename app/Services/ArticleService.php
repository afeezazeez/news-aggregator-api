<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleService
{


    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public function fetchAndStoreArticles(string $source): void
    {
        $sourcesMapping = $this->getNewsSourcesMapping();

        $sources =  $this->getNewsSources();

        if (!in_array($source, $sources)) {
            Log::warning("[Article Service] News source '{$source}' not found.");
            return;
        }

        try {
            $service = app($sourcesMapping[$source]);

            $articles = $service->fetchArticles();
            foreach ($articles as $article) {
                Article::updateOrCreate(
                    ['unique_id' => $article['unique_id'], 'source' => $source],
                    $article
                );
            }
            clear_cached_articles();
            Cache::forget('article_filters');

        } catch (\Exception $e) {
            Log::error("[Article Service] Failed to fetch articles from '{$source}': " . $e);
        }
    }

    public function getArticles(array $filters = [],mixed $page_size): LengthAwarePaginator
    {
        $page = request('page', 1);
        $cacheKey = 'articles_' . md5(json_encode($filters)) . "_page_{$page}";

        store_cache_key($cacheKey);

        return Cache::remember($cacheKey, 600, function () use ($filters,$page_size) {
            return Article::filter($filters)
                ->orderBy('published_at', 'desc')
                ->paginate($page_size)
                ->appends(request()->query());
        });
    }


    public function getFilterOptions(): array
    {
        return Cache::remember('article_filters', 3600, function () {
            $filters = Article::query()
                ->selectRaw(
                    'GROUP_CONCAT(DISTINCT category) as categories,
                 GROUP_CONCAT(DISTINCT source) as sources,
                 GROUP_CONCAT(DISTINCT contributor) as authors'
                )
                ->first()
                ->toArray();

            return [
                'categories' => $filters['categories'] ? explode(',', $filters['categories']) : [],
                'sources'    => $filters['sources'] ? explode(',', $filters['sources']) : [],
                'authors'    => $filters['authors'] ? explode(',', $filters['authors']) : [],
            ];
        });
    }



    public function getNewsSources(): array
    {
        return array_keys(app('news.sources'));
    }

    public function getNewsSourcesMapping(): array
    {
        return app('news.sources');
    }
}
