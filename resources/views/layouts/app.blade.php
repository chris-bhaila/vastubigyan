<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        @include('partials.favicon')

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900">
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
                <header class="flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 lg:hidden print:hidden">
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

                <main class="flex-1 px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
                    <div class="mx-auto max-w-4xl">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
