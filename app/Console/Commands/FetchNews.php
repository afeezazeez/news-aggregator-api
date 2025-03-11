<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchNews extends Command
{

    public function __construct(protected ArticleService $articleService)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to regularly fetch news from app sources';

    /**
     * Execute the console command.
     */
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sources = $this->articleService->getNewsSources();
        $totalSources = count($sources);

        $this->newLine();
        $this->info("======================================");
        $this->info("🚀 Starting to fetch articles from all sources...");
        $this->info("======================================");

        $progressBar = $this->output->createProgressBar($totalSources);
        $progressBar->start();

        foreach ($sources as $source) {
            $this->newLine();
            $this->line("🔍 Fetching articles from: <fg=cyan>{$source}</>");
            $this->line("--------------------------------------");

            try {
                $this->articleService->fetchAndStoreArticles($source);
                $this->info("✅ Successfully fetched and stored articles from: <fg=green>{$source}</>");
            } catch (\Exception $e) {
                $this->error("❌ Failed fetching from {$source}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);
        $this->info("======================================");
        $this->info("🏁 Finished fetching articles.");
        $this->info("======================================");
        $this->newLine();
    }


}
