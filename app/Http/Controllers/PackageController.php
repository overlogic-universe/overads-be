<?php

namespace App\Http\Controllers;

use App\Models\Package;

class PackageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/packages",
     *     summary="Ambil daftar paket",
     *     tags={"Package"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List paket berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Starter"),
     *                     @OA\Property(property="price", type="integer", example=50000),
     *                     @OA\Property(property="credits", type="integer", example=100),
     *                     @OA\Property(property="created_at", type="string", example="2025-01-01T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-01-01T10:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'data' => Package::all(),
        ]);
    }
}
