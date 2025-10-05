<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Muxg Chat')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav>
        <h1><a href="/">Muxg Chat</a></h1>
        <div>
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Register</a>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        // CSRF token for AJAX requests
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    @yield('scripts')
</body>
</html>