<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Authentication controller for managing user sessions
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for unique username manually since we're using a different connection
        $existingUser = User::where('username', $request->username)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'username' => ['The username has already been taken.']
                ]
            ], 422);
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'hashed_password' => Hash::make($request->password),
                'avatar' => $request->avatar ?? 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg',
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at ? $user->created_at->toISOString() : now()->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and create session
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find user by username
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->hashed_password)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'error' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            // Create new session
            $session = Session::createForUser($user, $request->device_info);

            return response()->json([
                'message' => 'Login successful',
                'token' => $session->token,
                'expires_at' => $session->expires_at->toISOString(),
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user and delete session
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $session = EnsureSessionValid::getCurrentSession($request);
            
            if ($session) {
                $session->delete();
            }

            return response()->json([
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            $session = EnsureSessionValid::getCurrentSession($request);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->toISOString(),
                ],
                'session' => [
                    'id' => $session->id,
                    'expires_at' => $session->expires_at->toISOString(),
                    'device_info' => $session->device_info,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get user info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh session (extend expiry)
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $session = EnsureSessionValid::getCurrentSession($request);
            
            if (!$session) {
                return response()->json([
                    'message' => 'Session not found',
                    'error' => 'SESSION_NOT_FOUND'
                ], 404);
            }

            // Extend session by 7 days
            $session->update([
                'expires_at' => now()->addDays(7)
            ]);

            return response()->json([
                'message' => 'Session refreshed successfully',
                'expires_at' => $session->expires_at->toISOString(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to refresh session',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
