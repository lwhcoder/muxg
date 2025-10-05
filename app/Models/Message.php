<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Message model representing chat messages in the public schema
 * 
 * @property string $id
 * @property string $user_id
 * @property string $room_id
 * @property string $content
 * @property \Carbon\Carbon $created_at
 */
class Message extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'public.messages';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'content',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the room this message belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * Get the reactions to this message.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'message_id', 'id');
    }

    /**
     * Get the reaction counts for this message.
     */
    public function getReactionCounts(): array
    {
        return $this->reactions()
                    ->select('type')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray();
    }

    /**
     * Check if a user has reacted to this message with a specific type.
     */
    public function hasReactionFrom(User $user, string $type): bool
    {
        return $this->reactions()
                    ->where('user_id', $user->id)
                    ->where('type', $type)
                    ->exists();
    }

    /**
     * Check if the message belongs to a specific user.
     */
    public function belongsToUser(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}