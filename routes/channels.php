<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User-specific channel
Broadcast::channel('App.Models.User.{id}', function (User $user, string $id) {
    return $user->id === $id;
});

// Room presence channel - users can join if they are members of the room
Broadcast::channel('room.{roomId}', function (User $user, string $roomId) {
    $room = Room::find($roomId);
    
    if (!$room) {
        return false;
    }
    
    // Allow access to public rooms or private rooms where user is a member
    if ($room->isPublic() || $room->hasMember($user)) {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'avatar' => $user->avatar,
        ];
    }
    
    return false;
});

// Global online users channel (optional)
Broadcast::channel('online-users', function (User $user) {
    return [
        'id' => $user->id,
        'username' => $user->username,
        'avatar' => $user->avatar,
    ];
});
