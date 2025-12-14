<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAdImageJob;
use App\Models\Ad;
use App\Models\AdGeneration;
use App\Models\AdSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ads",
     *     summary="Get all ads",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        $user = Auth::user();

        $ads = Ad::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($ads);
    }

    /**
     * @OA\Post(
     *     path="/api/ads",
     *     summary="Create new ad",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","type"},
     *
     *             @OA\Property(property="name", type="string", example="Iklan Promo September"),
     *             @OA\Property(property="type", type="string", enum={"images","video"}, example="video"),
     *             @OA\Property(property="description", type="string", example="Deskripsi iklan"),
     *             @OA\Property(property="theme", type="string", example="dark"),
     *             @OA\Property(property="platforms", type="array", @OA\Items(type="string"), example={"tiktok", "facebook"}),
     *             @OA\Property(property="reference_media", type="string", example="https://example.com/image.jpg")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:images,video',
            'description' => 'nullable|string',
            'theme' => 'nullable|string',
            'platforms' => 'nullable|array',
            'reference_media' => 'nullable|string',
        ]);

        $ad = Ad::create([
            'user_id' => $user->id,
            ...$validated,
        ]);

        return response()->json($ad, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/ads/{id}",
     *     summary="Get ad detail",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */
    public function show($id)
    {
        $ad = Ad::findOrFail($id);

        return response()->json($ad);
    }

    /**
     * @OA\Put(
     *     path="/api/ads/{id}",
     *     summary="Update ad",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="type", type="string", enum={"images","video"}),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="theme", type="string"),
     *             @OA\Property(property="platforms", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="reference_media", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Updated")
     * )
     */
    public function update(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'type' => 'sometimes|in:images,video',
            'description' => 'nullable|string',
            'theme' => 'nullable|string',
            'platforms' => 'nullable|array',
            'reference_media' => 'nullable|string',
        ]);

        $ad->update($validated);

        return response()->json($ad);
    }

    /**
     * @OA\Delete(
     *     path="/api/ads/{id}",
     *     summary="Delete ad",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=204, description="Deleted")
     * )
     */
    public function destroy($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/ads/{id}/generate",
     *     summary="Generate AI image/video for ad",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Generation started",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="generation_id", type="integer", example=12),
     *             @OA\Property(property="status", type="string", example="processing")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Ad not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function generate(Ad $ads)
    {
        $generation = AdGeneration::create([
            'ads_id' => $ads->id,
            'prompt' => $ads->description,
        ]);

        dispatch(new GenerateAdImageJob($generation));

        return response()->json([
            'generation_id' => $generation->id,
            'status' => 'processing',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/ads/{id}/schedule",
     *     summary="Schedule ad posting to platforms",
     *     tags={"Ads"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ad ID",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"scheduled_at"},
     *
     *             @OA\Property(
     *                 property="scheduled_at",
     *                 type="string",
     *                 format="date-time",
     *                 example="2025-01-20T10:00:00Z"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad scheduled successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Scheduled")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Ad not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function schedule(Request $request, Ad $ads)
    {
        foreach ($ads->platforms as $platform) {
            AdSchedule::create([
                'ads_id' => $ads->id,
                'platform' => $platform,
                'scheduled_at' => $request->scheduled_at,
            ]);
        }

        return response()->json(['message' => 'Scheduled']);
    }
}
