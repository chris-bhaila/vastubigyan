@extends('layouts.app')

@section('title', 'Edit ' . $package->name . ' - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Edit {{ $package->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            Changes here never alter purchases or credit batches already created from this package.
        </p>
    </div>

    @if ($errors->any())
        <div class="animate-fade-in-up animation-delay-75 mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="animate-fade-in-up animation-delay-75 mt-6 max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500" for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $package->name) }}" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500" for="analysis_count">Analyses included</label>
                <input type="number" id="analysis_count" name="analysis_count" min="1" value="{{ old('analysis_count', $package->analysis_count) }}" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500" for="price">Price (Nrs.)</label>
                <input type="number" id="price" name="price" min="0" value="{{ old('price', $package->price) }}" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500" for="validity_days">Validity (days)</label>
                <input type="number" id="validity_days" name="validity_days" min="1" value="{{ old('validity_days', $package->validity_days) }}" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $package->is_active))>
                Active (shown and purchasable on the pricing page)
            </label>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
                Save
            </button>

            <a href="{{ route('admin.packages.index') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                Cancel
            </a>
        </div>
    </form>
@endsection
