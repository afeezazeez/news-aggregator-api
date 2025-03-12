<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleListResource;
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
     */
    public function index(ArticleFilterRequest $request): JsonResponse
    {
        $news = $this->articleService->getArticles($request->validated(), $this->page_size);
        return successResponse(ArticleListResource::collection($news)->resource);
    }

}
