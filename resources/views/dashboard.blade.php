@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

@php
    $roleIcons = [
        'Super Admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.951.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />',
        'Admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
        'User' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />',
    ];

    $delays = ['animation-delay-150', 'animation-delay-225', 'animation-delay-300'];
@endphp

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">Here's your account at a glance.</p>
    </div>

    @isset($userCount)
        <div class="animate-fade-in-up animation-delay-75 mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Registered users</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent/10 text-accent">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 100-8 4 4 0 000 8zm6 3a4 4 0 00-8 0" />
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-semibold tracking-tight">{{ $userCount }}</p>
        </div>
    @endisset

    @isset($roleCounts)
        <h2 class="animate-fade-in-up animation-delay-150 mt-8 text-sm font-medium text-gray-500">By role</h2>

        <div class="mt-3 grid grid-cols-1 gap-5 sm:grid-cols-3">
            @foreach ($roleCounts as $label => $count)
                <div class="animate-fade-in-up {{ $delays[$loop->index] ?? '' }} rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">{{ $label }}</p>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-accent/10 text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                {!! $roleIcons[$label] ?? $roleIcons['User'] !!}
                            </svg>
                        </span>
                    </div>
                    <p class="mt-4 text-3xl font-semibold tracking-tight">{{ $count }}</p>
                </div>
            @endforeach
        </div>
    @endisset

    <div class="animate-fade-in-up {{ isset($userCount) ? 'animation-delay-300' : 'animation-delay-75' }} mt-8 max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-accent/10 text-lg font-semibold text-accent">
                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate font-medium">{{ auth()->user()->name }}</p>
                <p class="truncate text-sm text-gray-500">{{ auth()->user()->email ?? auth()->user()->mobile }}</p>
            </div>
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
            <span class="text-gray-500">Role</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">{{ auth()->user()->role->label() }}</span>
        </div>
    </div>
@endsection
