<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Room management API controller
 */
class RoomController extends Controller
{
    /**
     * Display a listing of accessible rooms
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            $perPage = $request->get('per_page', 20);
            $perPage = min($perPage, 100);

            $rooms = Room::accessibleTo($user)
                        ->select(['id', 'name', 'description', 'visibility', 'created_at'])
                        ->withCount('members')
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage);

            $roomsData = $rooms->items();
            foreach ($roomsData as $room) {
                $room->is_member = $room->hasMember($user);
            }

            return response()->json([
                'message' => 'Rooms retrieved successfully',
                'rooms' => $roomsData,
                'pagination' => [
                    'current_page' => $rooms->currentPage(),
                    'last_page' => $rooms->lastPage(),
                    'per_page' => $rooms->perPage(),
                    'total' => $rooms->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'visibility' => 'required|in:public,private',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $room = Room::create([
                'name' => $request->name,
                'description' => $request->description ?? '',
                'visibility' => $request->visibility,
            ]);

            // Automatically add creator as member
            $room->addMember($user);

            return response()->json([
                'message' => 'Room created successfully',
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'visibility' => $room->visibility,
                    'created_at' => $room->created_at?->toISOString() ?? now()->toISOString(),
                    'members_count' => 1,
                    'is_member' => true,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified room
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($id);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            // Check if user can access this room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You are not a member of this private room',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $membersCount = $room->members()->count();
            $messagesCount = $room->messages()->count();

            return response()->json([
                'message' => 'Room retrieved successfully',
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'visibility' => $room->visibility,
                    'created_at' => $room->created_at?->toISOString() ?? now()->toISOString(),
                    'members_count' => $membersCount,
                    'messages_count' => $messagesCount,
                    'is_member' => $room->hasMember($user),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($id);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            // Only room members can update room info
            if (!$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You must be a member to update this room',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string|max:1000',
                'visibility' => 'sometimes|in:public,private',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->description ?? '';
            }
            if ($request->has('visibility')) {
                $updateData['visibility'] = $request->visibility;
            }

            if (!empty($updateData)) {
                $room->update($updateData);
            }

            return response()->json([
                'message' => 'Room updated successfully',
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'visibility' => $room->visibility,
                    'created_at' => $room->created_at?->toISOString() ?? now()->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified room
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($id);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            // Only room members can delete room (in a real app, you might want more restrictions)
            if (!$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You must be a member to delete this room',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $room->delete();

            return response()->json([
                'message' => 'Room deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete room',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
