<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\Message;
use App\Models\Room;
use App\Events\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Message management API controller
 */
class MessageController extends Controller
{
    /**
     * Display messages in a room
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

            $perPage = $request->get('per_page', 50);
            $perPage = min($perPage, 100);

            $messages = $room->messages()
                           ->with(['user:id,username,avatar', 'reactions.user:id,username'])
                           ->orderBy('created_at', 'desc')
                           ->paginate($perPage);

            $messagesData = $messages->items();
            $formattedMessages = [];

            foreach ($messagesData as $message) {
                $reactionCounts = $message->getReactionCounts();
                
                $formattedMessages[] = [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at?->toISOString() ?? now()->toISOString(),
                    'user' => [
                        'id' => $message->user->id,
                        'username' => $message->user->username,
                        'avatar' => $message->user->avatar,
                    ],
                    'reactions' => $reactionCounts,
                    'user_reactions' => $message->reactions()
                                              ->where('user_id', $user->id)
                                              ->pluck('type')
                                              ->toArray(),
                    'is_own_message' => $message->belongsToUser($user),
                ];
            }

            return response()->json([
                'message' => 'Messages retrieved successfully',
                'messages' => array_reverse($formattedMessages), // Show oldest first
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created message
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

            // Check if user can send messages to this room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You must be a member to send messages',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message = Message::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'content' => $request->input('content'),
            ]);

            // Load user relation
            $message->load('user:id,username,avatar');

            // Broadcast the new message to room members in real-time
            broadcast(new NewMessage($message))->toOthers();

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at?->toISOString() ?? now()->toISOString(),
                    'user' => [
                        'id' => $message->user->id,
                        'username' => $message->user->username,
                        'avatar' => $message->user->avatar,
                    ],
                    'reactions' => [],
                    'user_reactions' => [],
                    'is_own_message' => true,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified message
     */
    public function show(Request $request, string $messageId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $message = Message::with(['user:id,username,avatar', 'room:id,name,visibility', 'reactions.user:id,username'])
                             ->find($messageId);

            if (!$message) {
                return response()->json([
                    'message' => 'Message not found',
                    'error' => 'MESSAGE_NOT_FOUND'
                ], 404);
            }

            $room = $message->room;

            // Check if user can access this message's room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You are not a member of this private room',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $reactionCounts = $message->getReactionCounts();

            return response()->json([
                'message' => 'Message retrieved successfully',
                'data' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at?->toISOString() ?? now()->toISOString(),
                    'user' => [
                        'id' => $message->user->id,
                        'username' => $message->user->username,
                        'avatar' => $message->user->avatar,
                    ],
                    'room' => [
                        'id' => $room->id,
                        'name' => $room->name,
                        'visibility' => $room->visibility,
                    ],
                    'reactions' => $reactionCounts,
                    'user_reactions' => $message->reactions()
                                              ->where('user_id', $user->id)
                                              ->pluck('type')
                                              ->toArray(),
                    'is_own_message' => $message->belongsToUser($user),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified message
     */
    public function update(Request $request, string $messageId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $message = Message::with('room:id,name,visibility')->find($messageId);
            if (!$message) {
                return response()->json([
                    'message' => 'Message not found',
                    'error' => 'MESSAGE_NOT_FOUND'
                ], 404);
            }

            // Only message author can edit the message
            if (!$message->belongsToUser($user)) {
                return response()->json([
                    'message' => 'Forbidden - You can only edit your own messages',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $message->update([
                'content' => $request->input('content'),
            ]);

            // TODO: Broadcast the message update to room members

            return response()->json([
                'message' => 'Message updated successfully',
                'data' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at?->toISOString() ?? now()->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified message
     */
    public function destroy(Request $request, string $messageId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $message = Message::find($messageId);
            if (!$message) {
                return response()->json([
                    'message' => 'Message not found',
                    'error' => 'MESSAGE_NOT_FOUND'
                ], 404);
            }

            // Only message author can delete the message
            if (!$message->belongsToUser($user)) {
                return response()->json([
                    'message' => 'Forbidden - You can only delete your own messages',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $message->delete();

            // TODO: Broadcast the message deletion to room members

            return response()->json([
                'message' => 'Message deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
