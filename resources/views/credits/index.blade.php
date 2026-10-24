@extends('layouts.app')

@section('title', 'Credits - ' . config('app.name', 'Laravel'))

@php
    $typeStyles = [
        'purchase' => 'bg-green-100 text-green-700',
        'usage' => 'bg-gray-100 text-gray-700',
        'expiry' => 'bg-red-100 text-red-700',
        'refund' => 'bg-blue-100 text-blue-700',
        'admin_grant' => 'bg-purple-100 text-purple-700',
    ];
@endphp

@section('content')
    <div class="animate-fade-in-up flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Your credits</h1>
            <p class="mt-1 text-sm text-gray-500">Track your analysis balance and history.</p>
        </div>

        <a href="{{ route('pricing') }}" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
            Buy analyses
        </a>
    </div>

    @if ($lowCredits || $expiringSoon)
        <div class="animate-fade-in-up animation-delay-75 mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            @if ($lowCredits)
                You have {{ $availableCredits }} {{ Str::plural('credit', $availableCredits) }} left.
            @endif

            @if ($expiringSoon)
                Your soonest batch expires on {{ $nextExpiry->format('M j, Y') }}.
            @endif

            <a href="{{ route('pricing') }}" class="cursor-pointer font-medium underline">Buy more</a> to stay covered.
        </div>
    @endif

    <div class="animate-fade-in-up animation-delay-75 mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase">Available credits</p>
            <p class="mt-1 text-3xl font-semibold">{{ $availableCredits }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase">Tier</p>
            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $tier === App\Enums\Tier::Premium ? 'bg-accent/10 text-accent' : 'bg-gray-100 text-gray-700' }}">
                {{ $tier->label() }}
            </span>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase">Next expiry</p>
            <p class="mt-1 text-lg font-semibold">{{ $nextExpiry?->format('M j, Y') ?? '—' }}</p>
        </div>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8">
        <h2 class="text-lg font-semibold">Active batches</h2>

        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @forelse ($batches as $batch)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                    <p class="text-sm text-gray-700">{{ $batch->remaining }} / {{ $batch->total }} remaining</p>
                    <p class="text-sm text-gray-500">Expires {{ $batch->expires_at->format('M j, Y') }}</p>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-gray-400">No active batches.</p>
            @endforelse
        </div>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8">
        <h2 class="text-lg font-semibold">History</h2>

        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @forelse ($transactions as $transaction)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium">{{ $transaction->created_at->format('M j, Y') }}</p>
                        @if ($transaction->note)
                            <p class="truncate text-sm text-gray-500">{{ $transaction->note }}</p>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $typeStyles[$transaction->type->value] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $transaction->type->label() }}
                        </span>

                        <span class="text-sm font-semibold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-gray-700' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ $transaction->amount }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-gray-400">No credit activity yet.</p>
            @endforelse
        </div>

        @if ($transactions->hasPages())
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
