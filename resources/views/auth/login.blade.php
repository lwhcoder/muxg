@extends('layouts.app')

@section('title', 'Login - Muxg Chat')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                Welcome back
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Sign in to your Muxg Chat account
            </p>
        </div>
        
        <div class="card p-8 space-y-6">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Username
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        required
                        class="input @error('username') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="Enter your username"
                    >
                    @error('username')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="input @error('password') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <button type="submit" class="w-full btn-primary">
                        Sign in
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                        Register here
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection