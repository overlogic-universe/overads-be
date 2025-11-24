<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class AuthController extends Controller
{
    /**
     * @OA\Info(
    *     title="OverAds API",
    *     version="1.0.0"
    * )
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register user baru",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"full_name","business_name","phone","email","password"},
     *
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="business_name", type="string", example="Doe Store"),
     *             @OA\Property(property="phone", type="string", example="08123456789"),
     *             @OA\Property(property="email", type="string", example="example@mail.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User berhasil didaftarkan"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     * )
     */
    public function dapat(Request $req)
    {
        return response()->json(['user' => 'hello']);
    }

    public function register(RegisterRequest $req)
    {
        $data = $req->only(['full_name', 'business_name', 'phone', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);
        try {
            // code...
            $user = User::create($data);
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], 201);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json(['error' => $th->getMessage()], 0);

        }

    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", example="example@mail.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(LoginRequest $req)
    {
        $user = User::where('email', $req->email)->first();
        if (! $user || ! Hash::check($req->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     security={{"sanctum": {}}},
     *     tags={"Auth"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil"
     *     )
     * )
     */
    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
