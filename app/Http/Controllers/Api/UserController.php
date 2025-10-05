<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * User management API controller
 */
class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 20);
            $perPage = min($perPage, 100); // Max 100 users per page

            $users = User::select(['id', 'username', 'avatar', 'created_at'])
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage);

            return response()->json([
                'message' => 'Users retrieved successfully',
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::select(['id', 'username', 'avatar', 'created_at'])
                       ->find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Load additional data
            $roomsCount = $user->rooms()->count();
            $messagesCount = $user->messages()->count();

            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->toISOString(),
                    'rooms_count' => $roomsCount,
                    'messages_count' => $messagesCount,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $currentUser = EnsureSessionValid::getAuthenticatedUser($request);
            
            // Check if user is trying to update their own profile
            if ($currentUser->id !== $id) {
                return response()->json([
                    'message' => 'Forbidden - You can only update your own profile',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:255|unique:auth.users,username,' . $id,
                'password' => 'sometimes|string|min:6',
                'avatar' => 'sometimes|nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            
            if ($request->has('username')) {
                $updateData['username'] = $request->username;
            }
            
            if ($request->has('password')) {
                $updateData['hashed_password'] = Hash::make($request->password);
            }
            
            if ($request->has('avatar')) {
                $updateData['avatar'] = $request->avatar;
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $currentUser = EnsureSessionValid::getAuthenticatedUser($request);
            
            // Check if user is trying to delete their own account
            if ($currentUser->id !== $id) {
                return response()->json([
                    'message' => 'Forbidden - You can only delete your own account',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Delete all user sessions first
            $user->sessions()->delete();
            
            // Delete the user (cascade will handle related data)
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's rooms
     */
    public function rooms(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            $rooms = $user->rooms()
                         ->select(['id', 'name', 'description', 'visibility', 'created_at'])
                         ->withPivot('joined_at')
                         ->orderBy('room_members.joined_at', 'desc')
                         ->get()
                         ->map(function ($room) {
                             return [
                                 'id' => $room->id,
                                 'name' => $room->name,
                                 'description' => $room->description,
                                 'visibility' => $room->visibility,
                                 'created_at' => $room->created_at->toISOString(),
                                 'joined_at' => $room->pivot->joined_at,
                             ];
                         });

            return response()->json([
                'message' => 'User rooms retrieved successfully',
                'rooms' => $rooms
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
