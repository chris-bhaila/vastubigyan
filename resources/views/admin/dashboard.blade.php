<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Admin - {{ config('app.name', 'Laravel') }}</title>

        @include('partials.favicon')

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm text-center">
            <div class="mb-6 flex justify-center">
                @include('partials.logo')
            </div>

            <h1 class="mb-2 font-medium">Admin dashboard</h1>
            <p class="mb-6 text-sm text-gray-500">Signed in as {{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</p>

            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-2xl font-semibold">{{ $userCount }}</p>
                <p class="text-sm text-gray-500">Registered users</p>
            </div>

            @can('viewLogViewer')
                <div class="mb-6 flex items-center justify-center gap-4 text-sm">
                    <a href="{{ route('activity-log.index') }}" class="cursor-pointer text-gray-500 hover:text-gray-700">Activity log</a>
                    <a href="{{ url(config('log-viewer.route_path', 'log-viewer')) }}" class="cursor-pointer text-gray-500 hover:text-gray-700">App logs</a>
                </div>
            @endcan

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="cursor-pointer rounded-md border border-gray-300 px-5 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                >
                    Log out
                </button>
            </form>
        </div>
    </body>
</html>
