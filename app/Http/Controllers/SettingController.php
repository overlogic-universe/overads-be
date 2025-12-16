<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/settings/apikey",
     *     summary="Get API key milik user",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="apiKey",
     *                 type="string",
     *                 nullable=false,
     *                 example="sk_live_newkey"
     *             ),
     *             @OA\Property(
     *                 property="igId",
     *                 type="string",
     *                 nullable=false,
     *                 example="1234567890"
     *             )
     *         )
     *     )
     * )
     */
    public function getApiKey(Request $request)
    {
        $setting = Setting::where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'apiKey' => $setting?->apiKey,
            'igId' => $setting?->igId,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/settings/apikey",
     *     summary="Update API key milik user",
     *     tags={"Settings"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             required={"value"},
     *
     *             @OA\Property(
     *                 property="igId",
     *                 type="string",
     *                 example="937468348374"
     *             ),
     *             @OA\Property(
     *                 property="apiKey",
     *                 type="string",
     *                 example="IIsHsMsssSKsDBsadsSaMDKsNSJmcmanNNcjasn"
     *             ),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="ok", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function updateApiKey(Request $request)
    {
        $request->validate([
            'apiKey' => 'required|string',
            'igId' => 'required|string',
        ]);

        Setting::updateOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'apiKey' => $request->apiKey,
                'igId' => $request->igId,
            ]
        );

        return response()->json(['success' => true]);
    }
}
