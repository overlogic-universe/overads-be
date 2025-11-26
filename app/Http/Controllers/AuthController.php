<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;

class AuthController extends Controller
{
    /**
     * @OA\Info(
     *     title="OverAds API",
     *     version="1.0.0"
     * )
     *
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register user baru",
     *     tags={"Auth"},
     *
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="application/json")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"full_name","business_name","phone","email","password"},
     *
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="business_name", type="string", example="Doe Store"),
     *             @OA\Property(property="phone", type="string", example="628123456789"),
     *             @OA\Property(property="email", type="string", example="example@mail.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User berhasil didaftarkan",
     *
     *         @OA\JsonContent()
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function register(RegisterRequest $req)
    {
        $data = $req->only(['full_name', 'business_name', 'phone', 'email', 'password']);
        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json(['user' => $user, 'token' => $token], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['error' => 'Email already exists'], 422);
            }

            return response()->json(['error' => $e], 500);

        } catch (\Throwable $th) {
            return response()->json(['error' => 'Internal server error'], 500);  // Perbaiki status code
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="application/json")
     *     ),
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
     *         description="Login Successful",
     *
     *         @OA\JsonContent()
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function login(LoginRequest $req)
    {
        $key = 'login-attempts-'.$req->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many login attempts. Please try again later.'], 429);
        }

        $user = User::where('email', $req->email)->first();
        if (! $user || ! Hash::check($req->password, $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        RateLimiter::clear($key);
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
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         required=true,
     *
     *         @OA\Schema(type="string", example="application/json")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout Successful",
     *
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get data user yang sedang login",
     *     tags={"Auth"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="string", example="63f2c0b8-1234-4f3c-a93f-88ffab123456"),
     *             @OA\Property(property="full_name", type="string", example="John Doe"),
     *             @OA\Property(property="business_name", type="string", example="Doe Store"),
     *             @OA\Property(property="phone", type="string", example="628123456789"),
     *             @OA\Property(property="email", type="string", example="example@mail.com"),
     *             @OA\Property(property="is_admin", type="boolean", example=false),
     *             @OA\Property(property="avatar_url", type="string", example="https://cdn.com/avatar.jpg"),
     *             @OA\Property(property="created_at", type="string"),
     *             @OA\Property(property="updated_at", type="string"),
     *         )
     *     )
     * )
     */
    public function getUser(Request $req)
    {
        $user = Auth::user();
        return response()->json($user);
    }
}
