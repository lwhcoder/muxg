<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reaction model representing message reactions in the public schema
 * 
 * @property string $id
 * @property string $user_id
 * @property string $message_id
 * @property string $type
 */
class Reaction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'public.reactions';

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
        'message_id',
        'type',
    ];

    /**
     * Common reaction types.
     */
    public const TYPES = [
        'like' => '👍',
        'love' => '❤️',
        'laugh' => '😂',
        'wow' => '😮',
        'sad' => '😢',
        'angry' => '😠',
    ];

    /**
     * Get the user who made this reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the message this reaction belongs to.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }

    /**
     * Get the emoji for this reaction type.
     */
    public function getEmoji(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Check if this reaction belongs to a specific user.
     */
    public function belongsToUser(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Scope to get reactions of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get reactions from a specific user.
     */
    public function scopeFromUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}