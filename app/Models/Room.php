<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Room model representing chat rooms in the public schema
 * 
 * @property string $id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property string $visibility
 */
class Room extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'public.rooms';

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
        'name',
        'description',
        'visibility',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the messages in this room.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'room_id', 'id');
    }

    /**
     * Get the members of this room.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'public.room_members', 'room_id', 'user_id')
                    ->withPivot('joined_at');
    }

    /**
     * Get the room member records.
     */
    public function roomMembers(): HasMany
    {
        return $this->hasMany(RoomMember::class, 'room_id', 'id');
    }

    /**
     * Scope a query to only include public rooms.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include private rooms.
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('visibility', 'private');
    }

    /**
     * Scope a query to include accessible rooms for a user.
     */
    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('visibility', 'public')
              ->orWhereHas('members', function ($memberQuery) use ($user) {
                  $memberQuery->where('auth.users.id', $user->id);
              });
        });
    }

    /**
     * Check if this is a public room.
     */
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    /**
     * Check if this is a private room.
     */
    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    /**
     * Check if a user is a member of this room.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('auth.users.id', $user->id)->exists();
    }

    /**
     * Add a user to this room.
     */
    public function addMember(User $user): RoomMember
    {
        if ($this->hasMember($user)) {
            throw new \Exception('User is already a member of this room');
        }

        return $this->roomMembers()->create([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Remove a user from this room.
     */
    public function removeMember(User $user): bool
    {
        return $this->roomMembers()
                    ->where('user_id', $user->id)
                    ->delete() > 0;
    }
}