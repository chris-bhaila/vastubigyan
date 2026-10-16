<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm text-center">
            <h1 class="mb-2 font-medium">Welcome, {{ auth()->user()->name }}</h1>
            <p class="mb-6 text-sm text-gray-500">{{ auth()->user()->email ?? auth()->user()->mobile }}</p>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="rounded-md border border-gray-300 px-5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Log out
                </button>
            </form>
        </div>
    </body>
</html>