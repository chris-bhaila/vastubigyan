@extends('layouts.app')

@section('title', 'Redirecting to eSewa - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up mx-auto max-w-sm text-center">
        <h1 class="mb-2 text-2xl font-semibold tracking-tight">Redirecting to eSewa…</h1>
        <p class="mb-6 text-sm text-gray-500">Please wait while we take you to eSewa to complete your payment.</p>

        <form action="{{ $formUrl }}" method="POST" id="esewa-checkout-form">
            @foreach ($fields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach

            <noscript>
                <button
                    type="submit"
                    class="cursor-pointer rounded-md bg-accent px-5 py-2 text-sm font-medium text-gray-900 transition hover:bg-accent/90"
                >
                    Continue to eSewa
                </button>
            </noscript>
        </form>
    </div>

    <script>
        document.getElementById('esewa-checkout-form').submit();
    </script>
@endsection
