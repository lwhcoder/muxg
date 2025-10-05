<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Session model representing user sessions in the auth schema
 * 
 * @property string $id
 * @property string $user_id
 * @property string $token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $expires_at
 * @property string|null $device_info
 */
class Session extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'auth.sessions';

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
        'token',
        'expires_at',
        'device_info',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($session) {
            if (!$session->token) {
                $session->token = Str::random(80);
            }
        });
    }

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Check if the session is valid (not expired).
     */
    public function isValid(): bool
    {
        return $this->expires_at > now();
    }

    /**
     * Check if the session is expired.
     */
    public function isExpired(): bool
    {
        return !$this->isValid();
    }

    /**
     * Create a new session for a user.
     */
    public static function createForUser(User $user, ?string $deviceInfo = null): self
    {
        return self::create([
            'user_id' => $user->id,
            'expires_at' => now()->addDays(7), // 7 days session lifetime
            'device_info' => $deviceInfo,
        ]);
    }

    /**
     * Find a valid session by token.
     */
    public static function findValidByToken(string $token): ?self
    {
        return self::where('token', $token)
                   ->where('expires_at', '>', now())
                   ->first();
    }
}