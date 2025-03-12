<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleListResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;


class ArticleController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index(ArticleFilterRequest $request): JsonResponse
    {
        $news =  $this->articleService->getArticles($request->validated());
        return successResponse(ArticleListResource::collection($news)->resource);
    }
}
