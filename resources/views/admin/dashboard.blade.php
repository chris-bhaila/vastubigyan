@extends('layouts.app')

@section('title', 'Admin - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Admin dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">
            Signed in as {{ auth()->user()->name }}
            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ auth()->user()->role->label() }}</span>
        </p>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="animate-fade-in-up animation-delay-75 rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
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
    </div>
@endsection
