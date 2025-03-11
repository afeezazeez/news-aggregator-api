<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('news.sources', function () {
            return [
                'newsapi' => \App\Services\NewsApiService::class,
                'guardian' => \App\Services\GuardianNewsService::class,
                'nytimes' => \App\Services\NytimesNewsService::class,
                'opennews' => \App\Services\OpenNewsService::class,
            ];
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
