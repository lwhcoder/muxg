<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the user has a valid session token
 */
class EnsureSessionValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from Authorization header (Bearer token)
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'message' => 'Unauthorized - Missing or invalid authorization header',
                'error' => 'MISSING_TOKEN'
            ], 401);
        }

        // Extract the token
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        if (empty($token)) {
            return response()->json([
                'message' => 'Unauthorized - Token is empty',
                'error' => 'EMPTY_TOKEN'
            ], 401);
        }

        // Find the session by token
        $session = Session::findValidByToken($token);
        
        if (!$session) {
            return response()->json([
                'message' => 'Unauthorized - Invalid or expired session token',
                'error' => 'INVALID_TOKEN'
            ], 401);
        }

        // Check if session is expired
        if ($session->isExpired()) {
            // Clean up expired session
            $session->delete();
            
            return response()->json([
                'message' => 'Unauthorized - Session has expired',
                'error' => 'EXPIRED_TOKEN'
            ], 401);
        }

        // Load the user and attach to request
        $user = $session->user;
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized - User not found',
                'error' => 'USER_NOT_FOUND'
            ], 401);
        }

        // Attach user and session to request for use in controllers
        $request->merge([
            'authenticated_user' => $user,
            'current_session' => $session,
        ]);

        // Set the authenticated user for Laravel's auth system
        auth()->guard()->setUser($user);

        return $next($request);
    }

    /**
     * Extract user from request (helper method for controllers)
     */
    public static function getAuthenticatedUser(Request $request): ?\App\Models\User
    {
        return $request->get('authenticated_user');
    }

    /**
     * Extract session from request (helper method for controllers)
     */
    public static function getCurrentSession(Request $request): ?\App\Models\Session
    {
        return $request->get('current_session');
    }
}
