<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Activity Log - {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 p-6">
        <div class="mx-auto max-w-4xl">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="font-medium">Activity Log</h1>
                <a href="{{ route('dashboard') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">Back to dashboard</a>
            </div>

            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">When</th>
                            <th class="px-4 py-2 font-medium">Event</th>
                            <th class="px-4 py-2 font-medium">Caused by</th>
                            <th class="px-4 py-2 font-medium">Properties</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-gray-500">{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2">{{ $activity->description }}</td>
                                <td class="px-4 py-2">{{ $activity->causer?->name ?? 'Guest' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $activity->properties->toJson() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">No activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $activities->links() }}
            </div>
        </div>
    </body>
</html>
