<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\RoomController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Room routes
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [RoomController::class, 'index'])->name('index');
        Route::get('/create', [RoomController::class, 'create'])->name('create');
        Route::post('/', [RoomController::class, 'store'])->name('store');
        Route::get('/{id}', [RoomController::class, 'show'])->name('show');
    });
    
    // User routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
    });
    
    // Profile route
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    
});

// Broadcasting authentication (for Laravel Echo)
Broadcast::routes(['middleware' => ['auth']]);
