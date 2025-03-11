<?php

namespace App\Interfaces;

interface NewsSourceInterface
{
    public function fetchArticles(): array;
    public function normalizeArticles(array $articles): array;
}
