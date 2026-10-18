<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Log in - {{ config('app.name', 'Laravel') }}</title>

        @include('partials.favicon')

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex justify-center">
                @include('partials.logo')
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
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-accent focus:outline-none focus:ring focus:ring-accent/20"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                autocomplete="current-password"
                                required
                                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-10 text-sm text-gray-900 focus:border-accent focus:outline-none focus:ring focus:ring-accent/20"
                            >
                            <button
                                type="button"
                                data-toggle-password="password"
                                aria-label="Show password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3 text-gray-400 hover:text-gray-600"
                            >
                                <svg data-icon-show class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg data-icon-hide class="hidden h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="cursor-pointer accent-accent rounded border-gray-300">
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full cursor-pointer rounded-md bg-accent px-5 py-2 text-sm font-medium text-gray-900 transition hover:bg-accent/90"
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
                    class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
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
