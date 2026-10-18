<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Log in - {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="mb-6 text-center">
                <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</a>
            </div>

            <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6">
                <h1 class="mb-6 text-center font-medium">Log in to your account</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-4 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="mobile" class="mb-1 block text-sm font-medium text-gray-700">Mobile number</label>
                        <input
                            id="mobile"
                            type="text"
                            name="mobile"
                            value="{{ old('mobile') }}"
                            autocomplete="tel"
                            autofocus
                            required
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-400 focus:outline-none focus:ring focus:ring-amber-100"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-amber-400 focus:outline-none focus:ring focus:ring-amber-100"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300">
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-gray-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-gray-800"
                    >
                        Log in
                    </button>
                </form>

                <div class="my-6 flex items-center gap-3 text-xs text-gray-400">
                    <span class="h-px flex-1 bg-gray-200"></span>
                    <span>OR</span>
                    <span class="h-px flex-1 bg-gray-200"></span>
                </div>

                <a
                    href="{{ route('auth.google.redirect') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.3h6.47c-.28 1.5-1.13 2.77-2.4 3.62v3.01h3.86c2.26-2.09 3.56-5.17 3.56-8.66z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3.01c-1.07.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.29v3.11C3.26 21.3 7.31 24 12 24z"/>
                        <path fill="#FBBC05" d="M5.27 14.27c-.24-.72-.38-1.49-.38-2.27s.14-1.55.38-2.27V6.62H1.29A11.97 11.97 0 0 0 0 12c0 1.93.46 3.76 1.29 5.38l3.98-3.11z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.98 3.11C6.22 6.86 8.87 4.75 12 4.75z"/>
                    </svg>
                    Continue with Google
                </a>
            </div>
        </div>
    </body>
</html>
