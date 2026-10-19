@extends('layouts.app')

@section('title', 'Purchase - ' . config('app.name', 'Laravel'))

@php
    $statusStyles = [
        'paid' => 'bg-green-100 text-green-700',
        'pending' => 'bg-gray-100 text-gray-700',
        'failed' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-gray-100 text-gray-700',
    ];
@endphp

@section('content')
    <div class="animate-fade-in-up max-w-sm">
        <h1 class="mb-1 text-2xl font-semibold tracking-tight">{{ $purchase->package->name }}</h1>
        <p class="mb-6 text-sm text-gray-500">Nrs. {{ number_format($purchase->amount) }}</p>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium {{ $statusStyles[$purchase->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $purchase->status->label() }}
            </span>

            @if ($purchase->status->value === 'paid')
                <p class="mt-4 text-sm text-gray-500">Paid on {{ $purchase->paid_at->format('M j, Y') }}. Your credits are ready to use.</p>

                <a href="{{ route('purchases.invoice', $purchase) }}" class="mt-4 inline-block cursor-pointer text-sm font-medium text-accent hover:underline">
                    View invoice
                </a>
            @elseif ($purchase->status->value === 'pending')
                <p class="mt-4 text-sm text-gray-500">We're still confirming this payment with eSewa. This page will show "Paid" once it's done — try refreshing in a minute.</p>
            @elseif ($purchase->status->value === 'failed')
                <p class="mt-4 text-sm text-gray-500">This payment didn't complete. No credits were added.</p>
            @endif
        </div>

        <div class="mt-6 flex items-center gap-4 text-sm">
            <a href="{{ route('purchases.index') }}" class="cursor-pointer text-gray-500 hover:text-gray-700">
                Purchase history
            </a>
            <a href="{{ route('dashboard') }}" class="cursor-pointer text-gray-500 hover:text-gray-700">
                Back to dashboard
            </a>
        </div>
    </div>
@endsection
