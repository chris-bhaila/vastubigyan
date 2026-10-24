@extends('layouts.app')

@section('title', 'Purchases - ' . config('app.name', 'Laravel'))

@php
    $statusStyles = [
        'paid' => 'bg-green-100 text-green-700',
        'pending' => 'bg-gray-100 text-gray-700',
        'failed' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-gray-100 text-gray-700',
    ];
@endphp

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Purchases</h1>
        <p class="mt-1 text-sm text-gray-500">Every credit package bought, across all users.</p>
    </div>

    <form method="GET" action="{{ route('admin.purchases.index') }}" class="animate-fade-in-up animation-delay-75 mt-6 flex flex-wrap items-end gap-3">
        <div class="min-w-0 flex-1">
            <label class="block text-xs font-medium text-gray-500" for="search">Search</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Transaction UUID, gateway ref, or buyer email"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none"
            >
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500" for="status">Status</label>
            <select id="status" name="status" class="mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                <option value="">Any</option>
                @foreach (App\Enums\PurchaseStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>{{ $statusOption->label() }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
            Filter
        </button>

        @if ($search !== '' || $status)
            <a href="{{ route('admin.purchases.index') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                Clear
            </a>
        @endif
    </form>

    <div class="animate-fade-in-up animation-delay-75 mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @forelse ($purchases as $purchase)
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                <div class="min-w-0">
                    <p class="truncate font-medium">{{ $purchase->package->name }}</p>
                    <p class="text-sm text-gray-500">
                        <a href="{{ route('admin.users.show', $purchase->user) }}" class="cursor-pointer text-accent hover:underline">
                            {{ $purchase->user->name }}
                        </a>
                        ({{ $purchase->user->email ?? $purchase->user->mobile }}) ·
                        {{ $purchase->created_at->format('M j, Y') }} · Nrs. {{ number_format($purchase->amount) }}
                    </p>
                </div>

                <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$purchase->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $purchase->status->label() }}
                </span>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">No purchases match.</p>
        @endforelse
    </div>

    @if ($purchases->hasPages())
        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    @endif
@endsection
