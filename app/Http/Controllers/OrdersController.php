<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get paginated list of orders",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *      @OA\Parameter(
    *         name="page",
    *         in="query",
    *         description="Page number",
    *         required=false,
    *         @OA\Schema(type="integer", example=1)
    *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="package_id", type="integer", example=3),
     *                     @OA\Property(property="amount", type="integer", example=100000),
     *                     @OA\Property(property="status", type="string", example="success"),
     *                     @OA\Property(property="created_at", type="string", example="2025-11-25 12:00:00"),
     *                 )
     *             ),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="links", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="label", type="string"),
     *                     @OA\Property(property="active", type="boolean"),
     *                 )
     *             ),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer", example=42),
     *         )
     *     )
     * )
     */
    public function index(){
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return response()->json($orders);
    }
}
