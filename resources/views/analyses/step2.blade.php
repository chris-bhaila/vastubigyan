@extends('layouts.app')

@section('title', 'New analysis - Step 2 - ' . config('app.name', 'Laravel'))

@php
    $availableCredits = auth()->user()->availableCredits();
@endphp

@section('content')
    <div class="animate-fade-in-up flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-accent">Step 2 of 2: Land information</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Start a new analysis</h1>
        </div>

        <a href="{{ route('analyses.create') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
            &larr; Back
        </a>
    </div>

    <form method="POST" action="{{ route('analyses.store') }}" enctype="multipart/form-data" class="animate-fade-in-up animation-delay-75 mt-8 max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm" onsubmit="return window.__submitAnalysis(this)">
        @csrf
        <input type="hidden" name="request_key" value="{{ $requestKey }}">

        <h2 class="text-sm font-semibold text-gray-700 uppercase">Plot location</h2>
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-field name="country" label="Country" value="{{ old('country', 'Nepal') }}" maxlength="100" />
            <x-field name="state" label="State / Province" value="{{ old('state') }}" maxlength="100" />
            <x-field name="district" label="District" value="{{ old('district') }}" maxlength="100" />
            <x-field name="municipality" label="Municipality" value="{{ old('municipality') }}" maxlength="100" />
            <x-field name="ward" label="Ward" type="number" value="{{ old('ward') }}" inputmode="numeric" min="1" max="99" />
            <x-field name="village" label="Village" value="{{ old('village') }}" maxlength="100" />
            <x-field name="tole" label="Tole" value="{{ old('tole') }}" maxlength="100" />
        </div>

        <h2 class="mt-8 text-sm font-semibold text-gray-700 uppercase">Measurements</h2>
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-field name="plot_area_sqft" label="Plot area (sqft)" type="number" value="{{ old('plot_area_sqft') }}" inputmode="decimal" step="0.01" min="0.01" />
            <x-field name="length" label="Length (ft)" type="number" value="{{ old('length') }}" inputmode="decimal" step="0.01" min="0.01" />
            <x-field name="width" label="Width (ft)" type="number" value="{{ old('width') }}" inputmode="decimal" step="0.01" min="0.01" />
            <x-field name="plinth_area" label="Plinth area (sqft)" type="number" value="{{ old('plinth_area') }}" inputmode="decimal" step="0.01" min="0.01" />
            <x-field name="angula_length_inches" label="Angula length (inches)" type="number" value="{{ old('angula_length_inches') }}" inputmode="decimal" step="0.01" min="0.01" max="10" />
        </div>

        <h2 class="mt-8 text-sm font-semibold text-gray-700 uppercase">Dates</h2>
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-field name="visit_date" label="Visit date" type="date" value="{{ old('visit_date') }}" />

            <x-field
                name="silanyas_month"
                label="Shilanyas month"
                type="select"
                :options="config('analysis.months')"
                :value="old('silanyas_month')"
            />

            <x-field
                name="constructed_year"
                label="Constructed year"
                type="number"
                value="{{ old('constructed_year') }}"
                inputmode="numeric"
                min="{{ config('analysis.constructed_year_min') }}"
                max="{{ now()->year }}"
            />
        </div>

        <h2 class="mt-8 text-sm font-semibold text-gray-700 uppercase">Documents</h2>
        <p class="mt-1 text-xs text-gray-500">
            JPG or PNG, up to {{ number_format(config('analysis.files.max_kb') / 1024, 1) }} MB each.
        </p>
        <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-field name="plot_with_road" label="Plot with road (photo)" type="file" accept="image/jpeg,image/png" />
            <x-field name="floor_plan" label="Floor plan (photo)" type="file" accept="image/jpeg,image/png" />
        </div>

        <div class="mt-8 border-t border-gray-100 pt-6">
            <label class="flex items-start gap-2 text-sm text-gray-700">
                <input type="checkbox" name="confirm" value="1" required class="mt-0.5">
                <span>
                    I confirm these details are correct. I understand they cannot be changed after submission and
                    that 1 analysis will be used.
                </span>
            </label>
            @error('confirm')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <span class="text-sm text-gray-500">This will use 1 of your {{ $availableCredits }} {{ $availableCredits === 1 ? 'analysis' : 'analyses' }}</span>

            <button
                type="submit"
                data-submit-button
                class="cursor-pointer rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
            >
                Submit
            </button>
        </div>
    </form>

    <script>
        window.__submitAnalysis = function (form) {
            var button = form.querySelector('[data-submit-button]');
            button.disabled = true;
            button.textContent = 'Submitting...';
            return true;
        };
    </script>
@endsection
