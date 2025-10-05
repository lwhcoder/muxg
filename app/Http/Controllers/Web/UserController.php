<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): View
    {
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the user's profile.
     */
    public function profile(): View
    {
        $user = Auth::user();

        return view('users.profile', compact('user'));
    }
}