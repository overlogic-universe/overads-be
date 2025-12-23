<?php

namespace App\Http\Controllers;

use App\Models\AdSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ad-schedules",
     *     summary="Get list ad schedules",
     *     tags={"Ad Schedules"},
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
     *         name="platform",
     *         in="query",
     *         required=false,
     *         description="Filter by platform",
     *
     *         @OA\Schema(type="string", example="instagram")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad schedules found",
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
     *                     @OA\Property(property="platform", type="string", example="instagram"),
     *                     @OA\Property(property="scheduled_at", type="string", example="2025-12-16T10:00:00Z"),
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

        $credit = User::where('id', $user->id)->first();
        if ($user->credit <= 0) {
            throw new \Exception('Kredit rendah');
        }
        $query = AdSchedule::with(['ad', 'generation']);

        if ($request->filled('ads_id')) {
            $query->where('ads_id', $request->ads_id);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query
            ->where('user_id', $user->id)
            ->orderBy('scheduled_at', 'asc')
            ->paginate(10);

        // $query = AdSchedule::query();

        // if ($request->filled('ads_id')) {
        //     $query->where('ads_id', $request->ads_id);
        // }

        // if ($request->filled('platform')) {
        //     $query->where('platform', $request->platform);
        // }

        // $schedules = $query
        //     ->orderBy('scheduled_at', 'asc')
        //     ->paginate(10);

        return response()->json($schedules);
    }

    /**
     * @OA\Get(
     *     path="/api/ad-schedules/{id}",
     *     summary="Get ad schedule detail",
     *     tags={"Ad Schedules"},
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
     *         description="Ad schedule found",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="ads_id", type="integer", example=12),
     *             @OA\Property(property="platform", type="string", example="instagram"),
     *             @OA\Property(property="scheduled_at", type="string", example="2025-12-16T10:00:00Z"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Ad schedule not found"
     *     )
     * )
     */
    public function show($id)
    {
        $schedule = AdSchedule::findOrFail($id);

        return response()->json($schedule);
    }

    /**
     * @OA\Get(
     *     path="/api/ads/{ads_id}/schedules",
     *     summary="Get ad schedules by ad",
     *     tags={"Ad Schedules"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="ads_id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=12)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ad schedules found",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="ads_id", type="integer", example=12),
     *                 @OA\Property(property="platform", type="string", example="facebook"),
     *                 @OA\Property(property="scheduled_at", type="string", example="2025-12-16T10:00:00Z"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="updated_at", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function byAd($ads_id)
    {
        $schedules = AdSchedule::where('ads_id', $ads_id)
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json($schedules);
    }
}
