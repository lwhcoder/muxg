<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * User model representing users in the auth schema
 * 
 * @property string $id
 * @property string $username
 * @property string $hashed_password
 * @property string $avatar
 * @property \Carbon\Carbon $created_at
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'auth.users';

    /**
     * The connection name for the model.
     */
    protected $connection = 'auth';

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
        'username',
        'hashed_password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'hashed_password',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Set the password attribute.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['hashed_password'] = Hash::make($value);
    }

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->hashed_password;
    }

    /**
     * Get the name attribute (uses username as fallback).
     */
    public function getNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Get the user's sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id', 'id');
    }

    /**
     * Get the user's messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

    /**
     * Get the user's reactions.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'user_id', 'id');
    }

    /**
     * Get the rooms the user is a member of.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'public.room_members', 'user_id', 'room_id')
                    ->withPivot('joined_at');
    }

    /**
     * Get the room memberships.
     */
    public function roomMemberships(): HasMany
    {
        return $this->hasMany(RoomMember::class, 'user_id', 'id');
    }

    /**
     * Check if the user is a member of a specific room.
     */
    public function isMemberOf(string $roomId): bool
    {
        return $this->rooms()->where('public.rooms.id', $roomId)->exists();
    }
}
