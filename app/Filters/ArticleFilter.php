<?php

namespace App\Filters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ArticleFilter
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function apply(Builder $query): Builder
    {
        return  $query
            ->when(!empty($this->filters['categories']), function ($q) {
                $q->whereIn('category', explode(',', $this->filters['categories']));
            })
            ->when(!empty($this->filters['sources']), function ($q) {
                $q->whereIn('source', explode(',', $this->filters['sources']));
            })
            ->when(!empty($this->filters['authors']), function ($q) {
                $authors = explode(',', $this->filters['authors']);
                $q->where(function ($subQuery) use ($authors) {
                    foreach ($authors as $author) {
                        $subQuery->orWhere('contributor', 'LIKE', "%{$author}%");
                    }
                });
            })
            ->when(!empty($this->filters['date']), function ($q) {
                $q->whereDate('published_at', $this->filters['date']);
            })
            ->when(!empty($this->filters['date_from']), function ($q) {
                $q->whereDate('published_at', '>=', $this->filters['date_from']);
            })
            ->when(!empty($this->filters['date_to']), function ($q) {
                $q->whereDate('published_at', '<=', $this->filters['date_to']);
            })
            ->when(!empty($this->filters['date_from']) && !empty($this->filters['date_to']), function ($q) {
                $start = Carbon::parse($this->filters['date_from'])->startOfDay();
                $end = Carbon::parse($this->filters['date_to'])->endOfDay();
                $q->whereBetween('published_at', [$start, $end]);
            })
            ->when(!empty($this->filters['q']), function ($q) {
                $searchTerm = '%' . $this->filters['q'] . '%';
                $q->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->orWhere('title', 'LIKE', $searchTerm)
                        ->orWhere('content', 'LIKE', $searchTerm)
                        ->orWhere('category', 'LIKE', $searchTerm)
                        ->orWhere('source', 'LIKE', $searchTerm)
                        ->orWhere('contributor', 'LIKE', $searchTerm);
                });
            });

    }
}
