@extends('layouts.app')

@section('title', 'Invoice - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up mx-auto max-w-2xl">
        <div class="mb-6 flex items-center justify-between print:hidden">
            <a href="{{ route('purchases.show', $purchase) }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                &larr; Back to purchase
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-gray-900 transition hover:bg-accent/90"
            >
                Print / Save as PDF
            </button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm print:border-0 print:shadow-none">
            <div class="flex items-start justify-between border-b border-gray-100 pb-6">
                @include('partials.logo')

                <div class="text-right">
                    <h1 class="text-xl font-semibold tracking-tight">Invoice</h1>
                    <p class="text-sm text-gray-500">#{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-gray-500">Billed to</p>
                    <p class="mt-1 font-medium">{{ $purchase->user->name }}</p>
                    <p class="text-gray-500">{{ $purchase->user->email ?? $purchase->user->mobile }}</p>
                </div>

                <div class="text-right">
                    <p class="text-gray-500">Date paid</p>
                    <p class="mt-1 font-medium">{{ $purchase->paid_at->format('F j, Y') }}</p>
                </div>
            </div>

            <table class="mt-8 w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-500">
                        <th class="pb-2 font-medium">Description</th>
                        <th class="pb-2 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-4">
                            <p class="font-medium">{{ $purchase->package->name }}</p>
                            <p class="text-gray-500">{{ $purchase->package->analysis_count }} analysis credit(s), valid {{ $purchase->package->validity_days }} days from payment</p>
                        </td>
                        <td class="py-4 text-right font-medium">Nrs. {{ number_format($purchase->amount) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="pt-4 text-right font-medium">Total paid</td>
                        <td class="pt-4 text-right text-lg font-semibold">Nrs. {{ number_format($purchase->amount) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-8 grid grid-cols-2 gap-6 border-t border-gray-100 pt-6 text-sm">
                <div>
                    <p class="text-gray-500">Payment method</p>
                    <p class="mt-1 font-medium capitalize">{{ $purchase->gateway }}</p>
                </div>

                <div class="text-right">
                    <p class="text-gray-500">Transaction reference</p>
                    <p class="mt-1 font-medium">{{ $purchase->gateway_ref ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
