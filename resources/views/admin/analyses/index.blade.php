@extends('layouts.app')

@section('title', 'Analyses - ' . config('app.name', 'Laravel'))

@php
    $statusStyles = [
        'started' => 'bg-gray-100 text-gray-700',
        'completed' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Analyses</h1>
        <p class="mt-1 text-sm text-gray-500">Every analysis run, across all users.</p>
    </div>

    <form method="GET" action="{{ route('admin.analyses.index') }}" class="animate-fade-in-up animation-delay-75 mt-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500" for="status">Status</label>
            <select id="status" name="status" class="mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                <option value="">Any</option>
                @foreach (App\Enums\AnalysisStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
            Filter
        </button>

        @if ($status)
            <a href="{{ route('admin.analyses.index') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                Clear
            </a>
        @endif
    </form>

    <div class="animate-fade-in-up animation-delay-75 mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @forelse ($analyses as $analysis)
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                <div class="min-w-0">
                    <p class="font-medium">
                        <a href="{{ route('admin.users.show', $analysis->user) }}" class="cursor-pointer text-accent hover:underline">
                            {{ $analysis->user->name }}
                        </a>
                        · Analysis #{{ $analysis->id }}
                    </p>
                    <p class="text-sm text-gray-500">{{ $analysis->created_at->format('M j, Y') }}</p>
                </div>

                <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$analysis->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $analysis->status->label() }}
                </span>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">No analyses match.</p>
        @endforelse
    </div>

    @if ($analyses->hasPages())
        <div class="mt-4">
            {{ $analyses->links() }}
        </div>
    @endif
@endsection
