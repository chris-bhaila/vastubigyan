@extends('layouts.app')

@section('title', 'Purchase History - ' . config('app.name', 'Laravel'))

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
        <h1 class="text-2xl font-semibold tracking-tight">Purchase history</h1>
        <p class="mt-1 text-sm text-gray-500">
            Every credit package you've bought, and its invoice once paid.
        </p>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @forelse ($purchases as $purchase)
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                <div class="min-w-0">
                    <p class="truncate font-medium">{{ $purchase->package->name }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $purchase->created_at->format('M j, Y') }} · Nrs. {{ number_format($purchase->amount) }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-4">
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$purchase->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $purchase->status->label() }}
                    </span>

                    <a href="{{ route('purchases.show', $purchase) }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                        Details
                    </a>

                    @if ($purchase->status->value === 'paid')
                        <a href="{{ route('purchases.invoice', $purchase) }}" class="cursor-pointer text-sm font-medium text-accent hover:underline">
                            Invoice
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-sm text-gray-400">
                {{ "You haven't bought any credits yet." }}
            </p>
        @endforelse
    </div>

    @if ($purchases->hasPages())
        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    @endif
@endsection
