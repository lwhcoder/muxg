<?php

namespace App\Events;

use App\Models\Reaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcasted when a new reaction is added to a message
 */
class NewReaction implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reaction $reaction;

    /**
     * Create a new event instance.
     */
    public function __construct(Reaction $reaction)
    {
        $this->reaction = $reaction->load(['user:id,username,avatar', 'message:id,room_id']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('room.' . $this->reaction->message->room_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reaction.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'reaction' => [
                'id' => $this->reaction->id,
                'type' => $this->reaction->type,
                'emoji' => $this->reaction->getEmoji(),
                'message_id' => $this->reaction->message_id,
                'user' => [
                    'id' => $this->reaction->user->id,
                    'username' => $this->reaction->user->username,
                    'avatar' => $this->reaction->user->avatar,
                ],
            ],
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function shouldBroadcast(): bool
    {
        return true;
    }
}
