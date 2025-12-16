<?php

namespace App\Http\Controllers;

use App\Models\AdGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdGenerationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ads-generations",
     *     summary="Get list ad generations",
     *     tags={"Ad Generations"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="ads_id",
     *         in="query",
     *         required=false,
     *         description="Filter by Ad ID",
     *
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by generation status",
     *
     *         @OA\Schema(type="string", enum={"pending","processing","generated","failed"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad generations found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *
     *                 @OA\Items(
     *                     type="object",
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="ads_id", type="integer", example=12),
     *                     @OA\Property(property="type", type="string", example="image"),
     *                     @OA\Property(property="prompt", type="string", example="Poster promo diskon 50%"),
     *                     @OA\Property(property="status", type="string", example="generated"),
     *                     @OA\Property(property="result_media", type="string", example="https://cdn.com/result.jpg"),
     *                     @OA\Property(property="payload", type="object", nullable=true),
     *                     @OA\Property(property="created_at", type="string"),
     *                     @OA\Property(property="updated_at", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AdGeneration::query();

        if ($request->filled('ads_id')) {
            $query->where('ads_id', $request->ads_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $generations = $query
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($generations);
    }

    /**
     * @OA\Get(
     *     path="/api/ads-generations/{id}",
     *     summary="Get ad generation detail",
     *     tags={"Ad Generations"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad generation found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="ads_id", type="integer", example=12),
     *             @OA\Property(property="type", type="string", example="image"),
     *             @OA\Property(property="prompt", type="string", example="Poster promo diskon 50%"),
     *             @OA\Property(property="status", type="string", example="processing"),
     *             @OA\Property(property="result_media", type="string", nullable=true),
     *             @OA\Property(property="payload", type="object", nullable=true),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ad generation not found"
     *     )
     * )
     */
    public function show($id)
    {
        $generation = AdGeneration::findOrFail($id);

        return response()->json($generation);
    }

    /**
     * @OA\Get(
     *     path="/api/ads/{ads_id}/generations",
     *     summary="Get ad generations by ad",
     *     tags={"Ad Generations"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="ads_id",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad generations found",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="ads_id", type="integer", example=12),
     *                 @OA\Property(property="type", type="string", example="image"),
     *                 @OA\Property(property="status", type="string", example="generated"),
     *                 @OA\Property(property="result_media", type="string", example="https://cdn.com/result.jpg"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function byAd($ads_id)
    {
        $generations = AdGeneration::where('ads_id', $ads_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($generations);
    }
}
