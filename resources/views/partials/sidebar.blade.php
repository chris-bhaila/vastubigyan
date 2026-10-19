@php
    $navLinks = collect([
        [
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'active' => request()->routeIs('dashboard'),
            'icon' => 'home',
        ],
    ]);

    $navLinks->push([
        'label' => 'Purchases',
        'route' => 'purchases.index',
        'active' => request()->routeIs('purchases.*'),
        'icon' => 'receipt',
    ]);

    $icons = [
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7m-9-2v10a1 1 0 001 1h3m6-11l2 2m-8-9v10a1 1 0 001 1h3m0-1v-4a1 1 0 011-1h2a1 1 0 011 1v4m0 0h3a1 1 0 001-1v-10" />',
        'tag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5.586a1 1 0 01.707.293l7.414 7.414a1 1 0 010 1.414l-7.586 7.586a1 1 0 01-1.414 0L4.293 12.293A1 1 0 014 11.586V5a2 2 0 012-2z" />',
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4m3 9l-1.5-1.5L15 21l-1.5-1.5L12 21l-1.5-1.5L9 21l-1.5-1.5L6 21V5a2 2 0 012-2h8a2 2 0 012 2v16z" />',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'terminal' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
        'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 5v1a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h5a2 2 0 012 2v1" />',
    ];
@endphp

<aside
    data-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full flex-col overflow-y-auto border-r border-gray-200 bg-white p-6 transition-[transform,width,padding] duration-300 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 print:hidden"
>
    <div class="mb-8 flex items-center justify-between">
        <div data-sidebar-text class="min-w-0">
            @include('partials.logo')
        </div>

        <button
            type="button"
            data-sidebar-collapse-toggle
            class="hidden shrink-0 cursor-pointer rounded-md p-1.5 text-gray-400 transition-all duration-300 hover:bg-gray-100 hover:text-gray-700 lg:inline-flex"
            aria-label="Collapse sidebar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 text-sm">
        @foreach ($navLinks as $link)
            <a
                href="{{ route($link['route']) }}"
                title="{{ $link['label'] }}"
                class="group relative flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-all duration-150 {{ $link['active'] ? 'bg-accent/10 text-accent' : 'text-gray-500 hover:translate-x-0.5 hover:bg-gray-50 hover:text-gray-700' }}"
            >
                @if ($link['active'])
                    <span class="absolute inset-y-1 left-0 w-0.5 rounded-full bg-accent"></span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons[$link['icon']] !!}
                </svg>
                <span data-sidebar-text>{{ $link['label'] }}</span>
            </a>
        @endforeach

        @can('viewLogViewer')
            <div class="my-3 border-t border-gray-100"></div>

            <a
                href="{{ route('activity-log.index') }}"
                title="Activity log"
                class="group relative flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-all duration-150 {{ request()->routeIs('activity-log.index') ? 'bg-accent/10 text-accent' : 'text-gray-500 hover:translate-x-0.5 hover:bg-gray-50 hover:text-gray-700' }}"
            >
                @if (request()->routeIs('activity-log.index'))
                    <span class="absolute inset-y-1 left-0 w-0.5 rounded-full bg-accent"></span>
                @endif
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['clock'] !!}
                </svg>
                <span data-sidebar-text>Activity log</span>
            </a>
            <a
                href="{{ url(config('log-viewer.route_path', 'log-viewer')) }}"
                title="App logs"
                class="group flex items-center gap-3 rounded-md px-3 py-2 font-medium text-gray-500 transition-all duration-150 hover:translate-x-0.5 hover:bg-gray-50 hover:text-gray-700"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['terminal'] !!}
                </svg>
                <span data-sidebar-text>App logs</span>
            </a>
        @endcan
    </nav>

    @unless (auth()->user()->isAdmin())
        <a
            href="{{ route('pricing') }}"
            title="Pricing"
            class="group relative flex items-center gap-3 rounded-md px-3 py-2 font-medium transition-all duration-150 {{ request()->routeIs('pricing') ? 'bg-accent/10 text-accent' : 'text-gray-500 hover:translate-x-0.5 hover:bg-gray-50 hover:text-gray-700' }}"
        >
            @if (request()->routeIs('pricing'))
                <span class="absolute inset-y-1 left-0 w-0.5 rounded-full bg-accent"></span>
            @endif
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                {!! $icons['tag'] !!}
            </svg>
            <span data-sidebar-text>Pricing</span>
        </a>
    @endunless

    <div class="mt-6 border-t border-gray-100 pt-4">
        <div class="mb-3 flex items-center gap-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent/10 text-sm font-semibold text-accent" title="{{ auth()->user()->name }}">
                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div data-sidebar-text class="min-w-0">
                <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-gray-500">{{ auth()->user()->email ?? auth()->user()->mobile }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                title="Log out"
                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-100"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['logout'] !!}
                </svg>
                <span data-sidebar-text>Log out</span>
            </button>
        </form>
    </div>
</aside>
