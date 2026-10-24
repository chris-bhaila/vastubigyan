@extends('layouts.app')

@section('title', 'Packages - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Packages</h1>
        <p class="mt-1 text-sm text-gray-500">The credit packages shown on the pricing page. Deactivate instead of deleting.</p>
    </div>

    <div class="animate-fade-in-up animation-delay-75 mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Analyses</th>
                    <th class="px-6 py-3">Price</th>
                    <th class="px-6 py-3">Validity</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($packages as $package)
                    <tr>
                        <td class="px-6 py-4 font-medium">{{ $package->name }}</td>
                        <td class="px-6 py-4">{{ $package->analysis_count }}</td>
                        <td class="px-6 py-4">Nrs. {{ number_format($package->price) }}</td>
                        <td class="px-6 py-4">{{ $package->validity_days }} days</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $package->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $package->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.packages.edit', $package) }}" class="cursor-pointer text-sm font-medium text-accent hover:underline">
                                Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No packages yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
