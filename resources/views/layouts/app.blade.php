<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Job Hub') }}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/x-icon" href="/favicon.svg">
        <!-- Inter Font from CDN with font-display: swap -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Lucide Icons CDN -->
        <script src="https://unpkg.com/lucide@latest"></script>
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-background text-foreground overflow-x-hidden">
        <!-- Skip Navigation Link -->
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary focus:text-white focus:rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
            Skip to main content
        </a>
        <main id="main-content">
            @yield('content')
        </main>

        <x-site-footer />

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.lucide) { lucide.createIcons(); }
            });
        </script>
    </body>
</html>
