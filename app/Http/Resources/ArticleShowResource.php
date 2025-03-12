<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'source' => $this->source,
            'slug' => $this->slug,
            'title' => $this->title,
            'url' => $this->url,
            'category' => $this->category,
            'contributor' => $this?->contributor,
            'published_at' => Carbon::parse($this->published_at)->format('jS F, Y H:i'),
            'content' => $this->content
        ];
    }
}
