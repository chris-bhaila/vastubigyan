@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Welcome, {{ auth()->user()->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">Here's your account at a glance.</p>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8 max-w-sm rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
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
