<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Web authentication controller
 */
class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Find user by username
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->hashed_password)) {
                return back()->withErrors([
                    'username' => 'Invalid credentials'
                ])->withInput();
            }

            // Create new session
            $session = Session::createForUser($user, $request->header('User-Agent'));

            // Store session token in cookie/session for web usage
            session(['auth_token' => $session->token]);
            
            // Login user in Laravel's auth system
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Login successful!');

        } catch (\Exception $e) {
            return back()->withErrors([
                'username' => 'Login failed. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Show the registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Handle registration form submission
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:auth.users,username',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'hashed_password' => Hash::make($request->password),
                'avatar' => 'https://static.vecteezy.com/system/resources/previews/009/292/244/non_2x/default-avatar-icon-of-social-media-user-vector.jpg',
            ]);

            // Create session and login
            $session = Session::createForUser($user, $request->header('User-Agent'));
            session(['auth_token' => $session->token]);
            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome to Muxg Chat!');

        } catch (\Exception $e) {
            return back()->withErrors([
                'username' => 'Registration failed. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        try {
            // Find and delete the session
            $token = session('auth_token');
            if ($token) {
                $session = Session::where('token', $token)->first();
                if ($session) {
                    $session->delete();
                }
            }

            // Clear session data
            session()->forget('auth_token');
            Auth::logout();

            return redirect()->route('login')->with('success', 'Logged out successfully!');

        } catch (\Exception $e) {
            return redirect()->route('login');
        }
    }
}
