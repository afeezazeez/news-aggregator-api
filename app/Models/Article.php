<?php

namespace App\Models;

use App\Filters\ArticleFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    protected $fillable = ['unique_id','title','content','source','category','contributor','published_at','url','slug'];


    /**
     * Scope a query to apply filters dynamically.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return (new ArticleFilter($filters))->apply($query);
    }

}
