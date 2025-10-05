<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomMemberController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\EnsureSessionValid;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (no middleware required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Routes that require authentication
    Route::middleware([EnsureSessionValid::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// All other API routes require authentication
Route::middleware([EnsureSessionValid::class])->group(function () {
    
    // User routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::patch('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::get('/{id}/rooms', [UserController::class, 'rooms']);
    });

    // Room routes
    Route::prefix('rooms')->group(function () {
        Route::get('/', [RoomController::class, 'index']);
        Route::post('/', [RoomController::class, 'store']);
        Route::get('/{id}', [RoomController::class, 'show']);
        Route::patch('/{id}', [RoomController::class, 'update']);
        Route::delete('/{id}', [RoomController::class, 'destroy']);

        // Room member routes
        Route::prefix('{roomId}/members')->group(function () {
            Route::get('/', [RoomMemberController::class, 'index']);
            Route::post('/', [RoomMemberController::class, 'store']);
            Route::delete('/{userId}', [RoomMemberController::class, 'destroy']);
            Route::get('/check', [RoomMemberController::class, 'check']);
        });

        // Room message routes
        Route::prefix('{roomId}/messages')->group(function () {
            Route::get('/', [MessageController::class, 'index']);
            Route::post('/', [MessageController::class, 'store']);
        });
    });

    // Message routes
    Route::prefix('messages')->group(function () {
        Route::get('/{id}', [MessageController::class, 'show']);
        Route::patch('/{id}', [MessageController::class, 'update']);
        Route::delete('/{id}', [MessageController::class, 'destroy']);

        // Message reaction routes
        Route::prefix('{messageId}/reactions')->group(function () {
            Route::get('/', [ReactionController::class, 'index']);
            Route::post('/', [ReactionController::class, 'store']);
            Route::post('/toggle', [ReactionController::class, 'toggle']);
        });
    });

    // Reaction routes
    Route::prefix('reactions')->group(function () {
        Route::delete('/{id}', [ReactionController::class, 'destroy']);
    });

});

// Health check endpoint (no authentication required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});