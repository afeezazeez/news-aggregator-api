<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
            Cache::forget('articles');
            Cache::forget('article_filters');

        } catch (\Exception $e) {
            Log::error("[Article Service] Failed to fetch articles from '{$source}': " . $e);
        }
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
