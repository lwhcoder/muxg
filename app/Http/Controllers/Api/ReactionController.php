<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionValid;
use App\Models\Message;
use App\Models\Reaction;
use App\Events\NewReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Reaction management API controller
 */
class ReactionController extends Controller
{
    /**
     * List reactions for a message
     */
    public function index(Request $request, string $messageId): JsonResponse
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

            $room = $message->room;

            // Check if user can access this message's room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You are not a member of this private room',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $reactions = $message->reactions()
                               ->with('user:id,username,avatar')
                               ->orderBy('id', 'desc')
                               ->get()
                               ->map(function ($reaction) {
                                   return [
                                       'id' => $reaction->id,
                                       'type' => $reaction->type,
                                       'emoji' => $reaction->getEmoji(),
                                       'user' => [
                                           'id' => $reaction->user->id,
                                           'username' => $reaction->user->username,
                                           'avatar' => $reaction->user->avatar,
                                       ],
                                   ];
                               });

            $reactionCounts = $message->getReactionCounts();

            return response()->json([
                'message' => 'Reactions retrieved successfully',
                'reactions' => $reactions,
                'counts' => $reactionCounts,
                'total' => $reactions->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve reactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a reaction to a message
     */
    public function store(Request $request, string $messageId): JsonResponse
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

            $room = $message->room;

            // Check if user can react to messages in this room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You must be a member to react to messages',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reactionType = $request->input('type');

            // Check if user already has this reaction on this message
            $existingReaction = $message->reactions()
                                      ->where('user_id', $user->id)
                                      ->where('type', $reactionType)
                                      ->first();

            if ($existingReaction) {
                return response()->json([
                    'message' => 'You have already reacted with this type',
                    'error' => 'DUPLICATE_REACTION'
                ], 409);
            }

            $reaction = Reaction::create([
                'user_id' => $user->id,
                'message_id' => $message->id,
                'type' => $reactionType,
            ]);

            // Load user relation
            $reaction->load('user:id,username,avatar');

            // Broadcast the new reaction to room members
            broadcast(new NewReaction($reaction))->toOthers();

            return response()->json([
                'message' => 'Reaction added successfully',
                'reaction' => [
                    'id' => $reaction->id,
                    'type' => $reaction->type,
                    'emoji' => $reaction->getEmoji(),
                    'user' => [
                        'id' => $reaction->user->id,
                        'username' => $reaction->user->username,
                        'avatar' => $reaction->user->avatar,
                    ],
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add reaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a specific reaction
     */
    public function destroy(Request $request, string $reactionId): JsonResponse
    {
        try {
            $user = EnsureSessionValid::getAuthenticatedUser($request);
            
            $reaction = Reaction::with(['message.room:id,name,visibility'])->find($reactionId);
            if (!$reaction) {
                return response()->json([
                    'message' => 'Reaction not found',
                    'error' => 'REACTION_NOT_FOUND'
                ], 404);
            }

            // Only reaction author can remove their own reaction
            if (!$reaction->belongsToUser($user)) {
                return response()->json([
                    'message' => 'Forbidden - You can only remove your own reactions',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $reaction->delete();

            // TODO: Broadcast the reaction removal to room members

            return response()->json([
                'message' => 'Reaction removed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove reaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle a reaction on a message (add if not exists, remove if exists)
     */
    public function toggle(Request $request, string $messageId): JsonResponse
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

            $room = $message->room;

            // Check if user can react to messages in this room
            if ($room->isPrivate() && !$room->hasMember($user)) {
                return response()->json([
                    'message' => 'Forbidden - You must be a member to react to messages',
                    'error' => 'FORBIDDEN'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $reactionType = $request->input('type');

            // Check if user already has this reaction on this message
            $existingReaction = $message->reactions()
                                      ->where('user_id', $user->id)
                                      ->where('type', $reactionType)
                                      ->first();

            if ($existingReaction) {
                // Remove existing reaction
                $existingReaction->delete();
                
                return response()->json([
                    'message' => 'Reaction removed successfully',
                    'action' => 'removed',
                    'type' => $reactionType,
                ], 200);
            } else {
                // Add new reaction
                $reaction = Reaction::create([
                    'user_id' => $user->id,
                    'message_id' => $message->id,
                    'type' => $reactionType,
                ]);

                $reaction->load('user:id,username,avatar');

                // Broadcast the new reaction to room members
                broadcast(new NewReaction($reaction))->toOthers();

                return response()->json([
                    'message' => 'Reaction added successfully',
                    'action' => 'added',
                    'reaction' => [
                        'id' => $reaction->id,
                        'type' => $reaction->type,
                        'emoji' => $reaction->getEmoji(),
                        'user' => [
                            'id' => $reaction->user->id,
                            'username' => $reaction->user->username,
                            'avatar' => $reaction->user->avatar,
                        ],
                    ]
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle reaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
