@extends('layouts.app')

@section('title', 'New analysis - Step 1 - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <p class="text-sm font-semibold text-accent">Step 1 of 2: Private information</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Start a new analysis</h1>
        <p class="mt-1 text-sm text-gray-500">Tell us about yourself first.</p>
    </div>

    <form method="POST" action="{{ route('analyses.step1') }}" class="animate-fade-in-up animation-delay-75 mt-8 max-w-3xl rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <x-field name="first_name" label="First name" value="{{ $old['first_name'] ?? '' }}" autocomplete="given-name" maxlength="100" />
            <x-field name="last_name" label="Last name" value="{{ $old['last_name'] ?? '' }}" autocomplete="family-name" maxlength="100" />

            <x-field
                name="gender"
                label="Gender"
                type="select"
                :options="collect(config('analysis.genders'))->mapWithKeys(fn ($g) => [$g => ucfirst($g)])"
                :value="$old['gender'] ?? ''"
            />

            <x-field name="phone" label="Phone" type="tel" value="{{ $old['phone'] ?? '' }}" autocomplete="tel" placeholder="+977 98xxxxxxxx" />

            <div class="sm:col-span-2 lg:col-span-3">
                <x-field name="contact_address" label="Contact address" value="{{ $old['contact_address'] ?? '' }}" autocomplete="street-address" maxlength="255" />
            </div>

            <x-field
                name="birth_year"
                label="Birth year"
                type="number"
                value="{{ $old['birth_year'] ?? '' }}"
                inputmode="numeric"
                min="{{ config('analysis.birth_year_min') }}"
                max="{{ now()->year }}"
            />
            <x-field name="birth_month" label="Birth month" type="number" value="{{ $old['birth_month'] ?? '' }}" inputmode="numeric" min="1" max="12" />
            <x-field name="birth_day" label="Birth day" type="number" value="{{ $old['birth_day'] ?? '' }}" inputmode="numeric" min="1" max="31" />

            <x-field name="time_of_birth" label="Time of birth" type="time" value="{{ $old['time_of_birth'] ?? '' }}" />

            <div class="sm:col-span-2 lg:col-span-2">
                <x-field
                    name="nachhetra"
                    label="Nachhetra"
                    type="select"
                    :options="collect(config('analysis.nakshatras'))->mapWithKeys(fn ($n) => [$n => $n])"
                    :value="$old['nachhetra'] ?? ''"
                />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="cursor-pointer rounded-md bg-accent px-5 py-2.5 text-sm font-medium text-white hover:opacity-90">
                Continue to land information
            </button>
        </div>
    </form>
@endsection
