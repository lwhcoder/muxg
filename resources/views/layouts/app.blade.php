<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="auth-token" content="{{ session('auth_token') }}">
    @endauth
    <title>@yield('title', 'Muxg Chat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                            Muxg Chat
                        </a>
                    </h1>
                </div>
                
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            Welcome, <span class="font-medium">{{ auth()->user()->username }}</span>!
                        </span>
                        
                        <div class="hidden md:flex items-center space-x-1">
                            <a href="{{ route('rooms.index') }}" class="btn-ghost">Rooms</a>
                            <a href="{{ route('users.index') }}" class="btn-ghost">Users</a>
                            <a href="{{ route('profile') }}" class="btn-ghost">Profile</a>
                        </div>
                        
                        <!-- Theme Switcher -->
                        <button id="theme-toggle" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200" title="Toggle theme">
                            <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                        
                        <!-- Mobile menu button -->
                        <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-danger">Logout</button>
                        </form>
                    @else
                        <!-- Theme Switcher for guests -->
                        <button id="theme-toggle" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200" title="Toggle theme">
                            <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                            <svg class="w-5 h-5 block dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                        
                        <a href="{{ route('login') }}" class="btn-ghost">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary">Register</a>
                    @endauth
                </div>
            </div>
            
            <!-- Mobile menu -->
            @auth
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-2">
                    <a href="{{ route('rooms.index') }}" class="btn-ghost justify-start">Rooms</a>
                    <a href="{{ route('users.index') }}" class="btn-ghost justify-start">Users</a>
                    <a href="{{ route('profile') }}" class="btn-ghost justify-start">Profile</a>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert-success mb-6 animate-fade-in">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error mb-6 animate-fade-in">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            userId: @json(auth()->user()->id ?? null),
            username: @json(auth()->user()->username ?? null),
        };
        
        // Helper function to get auth token
        function getAuthToken() {
            return document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
        }
        
        // Helper function for authenticated API requests
        function makeAuthenticatedRequest(url, options = {}) {
            const authToken = getAuthToken();
            const defaultHeaders = {
                'Authorization': `Bearer ${authToken}`,
                'Accept': 'application/json'
            };
            
            return fetch(url, {
                ...options,
                headers: {
                    ...defaultHeaders,
                    ...options.headers
                }
            });
        }
        
        // Theme switcher functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            
            // Initialize theme
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            // Theme toggle handler
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const isDark = document.documentElement.classList.contains('dark');
                    
                    if (isDark) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    }
                });
            }
            
            // Mobile menu toggle
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>