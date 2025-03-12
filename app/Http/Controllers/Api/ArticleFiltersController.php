<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;

class ArticleFiltersController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }
    /**
     * @OA\Get(
     *     path="/api/filters",
     *     summary="Get list of article filter options",
     *     tags={"Filters"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "success": true,
     *                 "data": {
     *                     "categories": {"Arts", "Business", "Technology", "Environment", "Health"},
     *                     "sources": {"guardian", "newsapi", "nytimes"},
     *                     "authors": {"Aashna Jain", "Amanda Taub", "Ben Casselman", "Ana Swanson", "Cade Metz"}
     *                 },
     *                 "message": "Success"
     *             },
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="sources", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="authors", type="array", @OA\Items(type="string"))
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $filters =  $this->articleService->getFilterOptions();
        return successResponse($filters);
    }

}
