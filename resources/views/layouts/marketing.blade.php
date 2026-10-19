<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        @include('partials.favicon')

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700&family=Manrope:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;600&display=swap" rel="stylesheet">

        @auth
            @fonts
        @endauth

        @vite(array_filter([
            'resources/css/pricing.css',
            auth()->check() ? 'resources/css/app.css' : null,
            auth()->check() ? 'resources/js/app.js' : null,
        ]))
    </head>
    <body>
        @auth
            <div class="flex min-h-screen">
                @include('partials.sidebar')

                <script>
                    (function () {
                        try {
                            if (localStorage.getItem('sidebar-collapsed') !== 'true') {
                                return;
                            }

                            document.querySelector('[data-sidebar]')?.classList.add('lg:w-20', 'lg:p-3');
                            document.querySelectorAll('[data-sidebar-text]').forEach((el) => el.classList.add('hidden'));
                            document.querySelector('[data-sidebar-collapse-toggle]')?.classList.add('rotate-180');
                        } catch (e) {}
                    })();
                </script>

                <div
                    data-sidebar-overlay
                    class="fixed inset-0 z-30 hidden bg-gray-900/40 backdrop-blur-sm transition-opacity lg:hidden"
                ></div>

                <div class="flex min-h-screen flex-1 flex-col">
                    <header class="flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
                        <button
                            type="button"
                            data-sidebar-toggle
                            class="cursor-pointer rounded-md p-1.5 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                            aria-label="Toggle navigation"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        @include('partials.logo')
                    </header>

                    <main class="flex-1">
                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            @yield('content')
        @endauth
    </body>
</html>
