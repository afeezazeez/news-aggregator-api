<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleShowResource;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;


class ArticleController extends Controller
{
    private mixed $page_size;
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
        $this->page_size = (request()->has('perPage') && is_numeric(request('perPage'))) ? request('perPage') : config('app.default_pagination_size');

    }

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Get filtered list of articles",
     *     tags={"Articles"},
     *     @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         description="Comma-separated list of categories (e.g., 'Business,Technology')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sources",
     *         in="query",
     *         description="Comma-separated list of sources (e.g., 'guardian,newsapi')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="authors",
     *         in="query",
     *         description="Comma-separated list of authors (e.g., 'John Doe,Jane Doe')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Exact date filter (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Start date for range filtering (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="End date for range filtering (format: YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query for title, content, category, source, or contributor",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="pageSize",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="string", example="Business")
     *                 ),
     *                 @OA\Property(
     *                     property="sources",
     *                     type="array",
     *                     @OA\Items(type="string", example="guardian")
     *                 ),
     *                 @OA\Property(
     *                     property="authors",
     *                     type="array",
     *                     @OA\Items(type="string", example="John Doe")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Success")
     *         )
     *     )
     * )
     *  Fetch filtered articles based on request parameters.
     *
     * @param ArticleFilterRequest $request The validated filter request.
     * @return JsonResponse The JSON response containing the filtered articles.
     */
    public function index(ArticleFilterRequest $request): JsonResponse
    {
        $news = $this->articleService->getArticles( $this->page_size,$request->validated());
        return successResponse(ArticleListResource::collection($news)->resource);
    }


    /**
     * @OA\Get(
     *     path="/api/articles/{slug}",
     *     summary="Get a single article by slug",
     *     description="Fetches a single article based on the provided slug.",
     *     operationId="getSingleArticle",
     *     tags={"Articles"},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="The slug of the article",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="source", type="string", example="guardian"),
     *                 @OA\Property(property="slug", type="string", example="australia-news-live-pm-says-trump-tariffs-not"),
     *                 @OA\Property(property="title", type="string", example="Australia news live: PM says Trump tariffs ‘not a friendly act’"),
     *                 @OA\Property(property="url", type="string", example="https://www.theguardian.com/australia-news/live/2025/mar/12/australia-news-live"),
     *                 @OA\Property(property="category", type="string", example="Australia news"),
     *                 @OA\Property(property="contributor", type="string", example="Stephanie Convery"),
     *                 @OA\Property(property="published_at", type="string", example="12th March, 2025 01:42"),
     *                 @OA\Property(property="content", type="string", example="<div id='block-67d0e22b8f0879'>Content here</div>")
     *             ),
     *             @OA\Property(property="message", type="string", example="Success")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Article not found")
     *         )
     *     )
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $article = $this->articleService->getSingleArticle($slug);
        return successResponse(ArticleShowResource::make($article));
    }

}
