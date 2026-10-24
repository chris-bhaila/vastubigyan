@extends('layouts.app')

@section('title', 'Analyses - ' . config('app.name', 'Laravel'))

@php
    $statusStyles = [
        'started' => 'bg-gray-100 text-gray-700',
        'completed' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
    ];

    $availableCredits = auth()->user()->availableCredits();
@endphp

@section('content')
    <div class="animate-fade-in-up flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Analyses</h1>
            <p class="mt-1 text-sm text-gray-500">Every analysis you've started, newest first.</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ $availableCredits }} {{ Str::plural('credit', $availableCredits) }} left</span>

            @if ($availableCredits > 0)
                <a
                    href="{{ route('analyses.create') }}"
                    class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white transition hover:opacity-90"
                >
                    Start analysis
                </a>
            @else
                <div class="flex items-center gap-3">
                    <button type="button" disabled class="cursor-not-allowed rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-400">
                        Start analysis
                    </button>
                    <a href="{{ route('pricing') }}" class="cursor-pointer text-sm font-medium text-accent hover:underline">
                        Buy analyses
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @forelse ($analyses as $analysis)
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                <div class="min-w-0">
                    <p class="font-medium">Analysis #{{ $analysis->id }}</p>
                    <p class="text-sm text-gray-500">{{ $analysis->created_at->format('M j, Y') }}</p>
                </div>

                <div class="flex shrink-0 items-center gap-4">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$analysis->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $analysis->status->label() }}
                    </span>

                    <a href="{{ route('analyses.show', $analysis) }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                        Details
                    </a>
                </div>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">No analyses yet.</p>
        @endforelse
    </div>

    @if ($analyses->hasPages())
        <div class="mt-4">
            {{ $analyses->links() }}
        </div>
    @endif
@endsection
