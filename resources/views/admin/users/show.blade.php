@extends('layouts.app')

@section('title', $user->name . ' - ' . config('app.name', 'Laravel'))

@php
    $typeStyles = [
        'purchase' => 'bg-green-100 text-green-700',
        'usage' => 'bg-gray-100 text-gray-700',
        'expiry' => 'bg-red-100 text-red-700',
        'refund' => 'bg-blue-100 text-blue-700',
        'admin_grant' => 'bg-purple-100 text-purple-700',
    ];

    $purchaseStatusStyles = [
        'paid' => 'bg-green-100 text-green-700',
        'pending' => 'bg-gray-100 text-gray-700',
        'failed' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-gray-100 text-gray-700',
    ];

    $analysisStatusStyles = [
        'started' => 'bg-gray-100 text-gray-700',
        'completed' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
    <div class="animate-fade-in-up flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $user->email ?? $user->mobile }} · Joined {{ $user->created_at->format('M j, Y') }}
            </p>
        </div>

        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
            {{ $user->role->label() }}
        </span>
    </div>

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

    @can('manage-credits')
        <div class="animate-fade-in-up animation-delay-75 mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Grant credits</h2>

            @if ($errors->any())
                <div class="mt-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.credits.store', $user) }}" class="mt-4 flex flex-wrap items-end gap-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-500" for="count">Credits</label>
                    <input type="number" id="count" name="count" min="{{ config('credits.grant_count_min', 1) }}" max="{{ config('credits.grant_count_max', 100) }}" value="1" class="mt-1 w-24 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500" for="expiry_days">Expires in (days)</label>
                    <input type="number" id="expiry_days" name="expiry_days" min="{{ config('credits.grant_expiry_days_min', 1) }}" max="{{ config('credits.grant_expiry_days_max', 730) }}" value="{{ config('credits.grant_expiry_days_default', 365) }}" class="mt-1 w-28 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                </div>

                <div class="min-w-0 flex-1">
                    <label class="block text-xs font-medium text-gray-500" for="reason">Reason</label>
                    <input type="text" id="reason" name="reason" required placeholder="Why are these credits being granted?" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                </div>

                <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                    Grant
                </button>
            </form>
        </div>
    @endcan

    <div class="animate-fade-in-up animation-delay-75 mt-8">
        <h2 class="text-lg font-semibold">Ledger</h2>

        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @forelse ($transactions as $transaction)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium">{{ $transaction->created_at->format('M j, Y') }}</p>
                        <p class="truncate text-sm text-gray-500">
                            {{ $transaction->note }}
                            @if ($transaction->actor)
                                · by {{ $transaction->actor->name }}
                            @endif
                        </p>
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
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8">
        <h2 class="text-lg font-semibold">Purchases</h2>

        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @forelse ($purchases as $purchase)
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ $purchase->package->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $purchase->created_at->format('M j, Y') }} · Nrs. {{ number_format($purchase->amount) }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-4">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $purchaseStatusStyles[$purchase->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $purchase->status->label() }}
                        </span>

                        @can('manage-credits')
                            @if ($purchase->status->value === 'paid')
                                <details class="relative">
                                    <summary class="cursor-pointer text-sm font-medium text-red-600 hover:underline">Refund</summary>

                                    <form method="POST" action="{{ route('admin.purchases.refund', $purchase) }}" class="mt-3 w-72 rounded-md border border-gray-200 bg-white p-4 shadow-lg">
                                        @csrf

                                        <p class="text-xs text-amber-700">
                                            This removes the unused credits from this purchase and marks it refunded. It does not return money to the customer; do that separately in the eSewa merchant portal.
                                        </p>

                                        <label class="mt-3 block text-xs font-medium text-gray-500" for="reason-{{ $purchase->id }}">Reason</label>
                                        <input type="text" id="reason-{{ $purchase->id }}" name="reason" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">

                                        <label class="mt-3 flex items-center gap-2 text-xs text-gray-600">
                                            <input type="checkbox" name="confirm" value="1" required>
                                            I understand this does not refund the customer's money.
                                        </label>

                                        <button type="submit" class="mt-3 w-full cursor-pointer rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:opacity-90">
                                            Confirm refund
                                        </button>
                                    </form>
                                </details>
                            @endif
                        @endcan
                    </div>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-gray-400">No purchases yet.</p>
            @endforelse
        </div>

        @if ($purchases->hasPages())
            <div class="mt-4">{{ $purchases->links() }}</div>
        @endif
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-8">
        <h2 class="text-lg font-semibold">Analyses</h2>

        <div class="mt-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @forelse ($analyses as $analysis)
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-6 py-4 last:border-0">
                    <div class="min-w-0">
                        <p class="font-medium">Analysis #{{ $analysis->id }}</p>
                        <p class="text-sm text-gray-500">{{ $analysis->created_at->format('M j, Y') }}</p>
                    </div>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $analysisStatusStyles[$analysis->status->value] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $analysis->status->label() }}
                    </span>
                </div>
            @empty
                <p class="px-6 py-10 text-center text-sm text-gray-400">No analyses yet.</p>
            @endforelse
        </div>

        @if ($analyses->hasPages())
            <div class="mt-4">{{ $analyses->links() }}</div>
        @endif
    </div>
@endsection
