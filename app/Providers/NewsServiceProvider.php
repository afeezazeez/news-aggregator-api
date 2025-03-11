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
        $this->app->singleton('news.sources', function () {
            return [
                'guardian' => \App\Services\GuardianNewsService::class,
                'newsapi'  => \App\Services\NewsApiService::class
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
