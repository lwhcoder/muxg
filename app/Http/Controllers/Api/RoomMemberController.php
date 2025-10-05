<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Room membership management API controller
 */
class RoomMemberController extends Controller
{
    /**
     * List room members
     */
    public function index(Request $request, string $roomId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($roomId);
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

            $members = $room->members()
                          ->select(['auth.users.id', 'username', 'avatar', 'auth.users.created_at'])
                          ->withPivot('joined_at')
                          ->orderBy('room_members.joined_at', 'desc')
                          ->get()
                          ->map(function ($member) {
                              return [
                                  'id' => $member->id,
                                  'username' => $member->username,
                                  'avatar' => $member->avatar,
                                  'user_created_at' => $member->created_at->toISOString(),
                                  'joined_at' => $member->pivot->joined_at,
                              ];
                          });

            return response()->json([
                'message' => 'Room members retrieved successfully',
                'members' => $members,
                'count' => $members->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve room members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Join a room
     */
    public function store(Request $request, string $roomId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($roomId);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            // Check if user is already a member
            if ($room->hasMember($user)) {
                return response()->json([
                    'message' => 'You are already a member of this room',
                    'error' => 'ALREADY_MEMBER'
                ], 409);
            }

            // Add user to room
            $membership = $room->addMember($user);

            return response()->json([
                'message' => 'Successfully joined room',
                'membership' => [
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'joined_at' => $membership->joined_at,
                ],
                'room' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'visibility' => $room->visibility,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to join room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leave a room / Remove a member
     */
    public function destroy(Request $request, string $roomId, string $userId): JsonResponse
    {
        try {
            $currentUser = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($roomId);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            $targetUser = User::find($userId);
            if (!$targetUser) {
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Check if target user is a member
            if (!$room->hasMember($targetUser)) {
                return response()->json([
                    'message' => 'User is not a member of this room',
                    'error' => 'NOT_MEMBER'
                ], 404);
            }

            // Users can only remove themselves, unless they have admin privileges
            // For this simple implementation, users can only leave themselves
            if ($currentUser->id !== $targetUser->id) {
                return response()->json([
                    'message' => 'Forbidden - You can only leave rooms yourself',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            // Remove user from room
            $removed = $room->removeMember($targetUser);
            
            if (!$removed) {
                return response()->json([
                    'message' => 'Failed to remove member from room',
                    'error' => 'REMOVAL_FAILED'
                ], 500);
            }

            return response()->json([
                'message' => 'Successfully left room'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to leave room',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if current user is a member of a room
     */
    public function check(Request $request, string $roomId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $room = Room::find($roomId);
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found',
                    'error' => 'ROOM_NOT_FOUND'
                ], 404);
            }

            $isMember = $room->hasMember($user);
            $membership = null;

            if ($isMember) {
                $membershipRecord = $room->roomMembers()
                                       ->where('user_id', $user->id)
                                       ->first();
                
                if ($membershipRecord) {
                    $membership = [
                        'room_id' => $membershipRecord->room_id,
                        'user_id' => $membershipRecord->user_id,
                        'joined_at' => $membershipRecord->joined_at,
                    ];
                }
            }

            return response()->json([
                'message' => 'Membership status retrieved successfully',
                'is_member' => $isMember,
                'membership' => $membership,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check membership',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
