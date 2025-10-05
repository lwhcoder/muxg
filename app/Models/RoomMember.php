<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RoomMember model representing room membership in the public schema
 * 
 * @property string $room_id
 * @property string $user_id
 * @property \Carbon\Carbon $joined_at
 */
class RoomMember extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'public.room_members';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = ['room_id', 'user_id'];

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
        'room_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'joined_at' => 'datetime',
    ];

    /**
     * Get the room this membership belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * Get the user this membership belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Set the key name for the model.
     */
    protected function setKeysForSaveQuery($query)
    {
        $query
            ->where('room_id', $this->getAttribute('room_id'))
            ->where('user_id', $this->getAttribute('user_id'));

        return $query;
    }
}