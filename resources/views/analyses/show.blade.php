@extends('layouts.app')

@section('title', 'Analysis #' . $analysis->id . ' - ' . config('app.name', 'Laravel'))

@php
    $statusStyles = [
        'started' => 'bg-gray-100 text-gray-700',
        'completed' => 'bg-green-100 text-green-700',
        'failed' => 'bg-red-100 text-red-700',
    ];

    $input = $analysis->input;
@endphp

@section('content')
    <div class="animate-fade-in-up flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Analysis #{{ $analysis->id }}</h1>
            <p class="mt-1 text-sm text-gray-500">Submitted {{ $analysis->created_at->format('M j, Y, g:i a') }}</p>
        </div>

        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$analysis->status->value] ?? 'bg-gray-100 text-gray-700' }}">
            {{ $analysis->status->label() }}
        </span>
    </div>

    @if (session('status'))
        <div class="animate-fade-in-up animation-delay-75 mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($input)
        <div class="animate-fade-in-up animation-delay-75 mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 uppercase">Private information</h2>
            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-xs text-gray-500">First name</dt><dd class="mt-0.5 text-sm">{{ $input->first_name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Last name</dt><dd class="mt-0.5 text-sm">{{ $input->last_name }}</dd></div>
                <div><dt class="text-xs text-gray-500">Gender</dt><dd class="mt-0.5 text-sm">{{ ucfirst($input->gender) }}</dd></div>
                <div><dt class="text-xs text-gray-500">Phone</dt><dd class="mt-0.5 text-sm">{{ $input->phone }}</dd></div>
                <div class="sm:col-span-2 lg:col-span-3"><dt class="text-xs text-gray-500">Contact address</dt><dd class="mt-0.5 text-sm">{{ $input->contact_address }}</dd></div>
                <div><dt class="text-xs text-gray-500">Date of birth</dt><dd class="mt-0.5 text-sm">{{ sprintf('%04d-%02d-%02d', $input->birth_year, $input->birth_month, $input->birth_day) }}</dd></div>
                <div><dt class="text-xs text-gray-500">Time of birth</dt><dd class="mt-0.5 text-sm">{{ $input->time_of_birth }}</dd></div>
                <div><dt class="text-xs text-gray-500">Nachhetra</dt><dd class="mt-0.5 text-sm">{{ $input->nachhetra }}</dd></div>
            </dl>

            <h2 class="mt-8 text-sm font-semibold text-gray-700 uppercase">Land information</h2>
            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><dt class="text-xs text-gray-500">Country</dt><dd class="mt-0.5 text-sm">{{ $input->country }}</dd></div>
                <div><dt class="text-xs text-gray-500">State</dt><dd class="mt-0.5 text-sm">{{ $input->state }}</dd></div>
                <div><dt class="text-xs text-gray-500">District</dt><dd class="mt-0.5 text-sm">{{ $input->district }}</dd></div>
                <div><dt class="text-xs text-gray-500">Municipality</dt><dd class="mt-0.5 text-sm">{{ $input->municipality }}</dd></div>
                <div><dt class="text-xs text-gray-500">Ward</dt><dd class="mt-0.5 text-sm">{{ $input->ward }}</dd></div>
                <div><dt class="text-xs text-gray-500">Village</dt><dd class="mt-0.5 text-sm">{{ $input->village }}</dd></div>
                <div><dt class="text-xs text-gray-500">Tole</dt><dd class="mt-0.5 text-sm">{{ $input->tole }}</dd></div>
                <div><dt class="text-xs text-gray-500">Plot area (sqft)</dt><dd class="mt-0.5 text-sm">{{ $input->plot_area_sqft }}</dd></div>
                <div><dt class="text-xs text-gray-500">Length (ft)</dt><dd class="mt-0.5 text-sm">{{ $input->length }}</dd></div>
                <div><dt class="text-xs text-gray-500">Width (ft)</dt><dd class="mt-0.5 text-sm">{{ $input->width }}</dd></div>
                <div><dt class="text-xs text-gray-500">Plinth area (sqft)</dt><dd class="mt-0.5 text-sm">{{ $input->plinth_area }}</dd></div>
                <div><dt class="text-xs text-gray-500">Angula length (inches)</dt><dd class="mt-0.5 text-sm">{{ $input->angula_length_inches }}</dd></div>
                <div><dt class="text-xs text-gray-500">Visit date</dt><dd class="mt-0.5 text-sm">{{ $input->visit_date->format('M j, Y') }}</dd></div>
                <div><dt class="text-xs text-gray-500">Shilanyas month</dt><dd class="mt-0.5 text-sm">{{ config('analysis.months')[$input->silanyas_month] ?? $input->silanyas_month }}</dd></div>
                <div><dt class="text-xs text-gray-500">Constructed year</dt><dd class="mt-0.5 text-sm">{{ $input->constructed_year }}</dd></div>
            </dl>

            <h2 class="mt-8 text-sm font-semibold text-gray-700 uppercase">Documents</h2>
            <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs text-gray-500">Plot with road</p>
                    <a href="{{ route('analyses.files', [$analysis, 'plot-with-road']) }}" class="mt-2 block" target="_blank">
                        <img src="{{ route('analyses.files', [$analysis, 'plot-with-road']) }}" alt="Plot with road" class="h-32 w-full rounded-md border border-gray-200 object-cover">
                        <span class="mt-1 block text-xs font-medium text-accent hover:underline">Download</span>
                    </a>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Floor plan</p>
                    <a href="{{ route('analyses.files', [$analysis, 'floor-plan']) }}" class="mt-2 block" target="_blank">
                        <img src="{{ route('analyses.files', [$analysis, 'floor-plan']) }}" alt="Floor plan" class="h-32 w-full rounded-md border border-gray-200 object-cover">
                        <span class="mt-1 block text-xs font-medium text-accent hover:underline">Download</span>
                    </a>
                </div>
            </div>

            <p class="mt-8 border-t border-gray-100 pt-4 text-xs text-gray-400">
                Submitted details cannot be changed.
            </p>
        </div>
    @else
        <div class="animate-fade-in-up animation-delay-75 mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-gray-400">No submitted details found for this analysis.</p>
        </div>
    @endif
@endsection
