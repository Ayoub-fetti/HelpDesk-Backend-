<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
public function store(LoginRequest $request): JsonResponse
{
    $request->authenticate();

    $user = $request->user();
    $token = $user->createToken('API Token')->plainTextToken;

    return response()->json([
    'message' => 'Logged in successfully',
    'user' => $request->user(),
    'token' => $token,
]);
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        // Ensure the user is authenticated before proceeding
        if ($request->user()) {
            Auth::guard('web')->logout();

            // Check if the session exists before invalidating it
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json(['message' => 'Logout successful']);
        }

        return response()->json(['message' => 'No authenticated user found'], Response::HTTP_UNAUTHORIZED);
    }
}
