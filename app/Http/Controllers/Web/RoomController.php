<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index(): View
    {
        $rooms = Room::with(['members'])
            ->withCount('members')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create(): View
    {
        return view('rooms.create');
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $room = Room::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'visibility' => 'public', // Default to public
            'creator_id' => Auth::id(),
        ]);

        // Auto-join the creator as a member
        $room->members()->attach(Auth::id(), [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Room created successfully!');
    }

    /**
     * Display the specified room.
     */
    public function show(string $id): View
    {
        $room = Room::with(['members'])
            ->withCount('members')
            ->findOrFail($id);

        // Check if user is a member or room is public
        $isMember = $room->members()->where('user_id', Auth::id())->exists();
        
        if ($room->visibility === 'private' && !$isMember) {
            abort(403, 'You do not have access to this room.');
        }

        // Auto-join user to public rooms
        if ($room->visibility === 'public' && !$isMember) {
            $room->members()->attach(Auth::id(), [
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        return view('rooms.show', [
            'roomId' => $id,
            'room' => $room
        ]);
    }
}