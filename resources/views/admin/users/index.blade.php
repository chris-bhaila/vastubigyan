@extends('layouts.app')

@section('title', 'Users - ' . config('app.name', 'Laravel'))

@section('content')
    <div class="animate-fade-in-up">
        <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
        <p class="mt-1 text-sm text-gray-500">Every registered user, with their credit balance and tier.</p>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="animate-fade-in-up animation-delay-75 mt-6 flex flex-wrap items-end gap-3">
        <div class="min-w-0 flex-1">
            <label class="block text-xs font-medium text-gray-500" for="search">Search</label>
            <input
                type="text"
                id="search"
                name="search"
                value="{{ $search }}"
                placeholder="Name or email"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none"
            >
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500" for="role">Role</label>
            <select id="role" name="role" class="mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                <option value="">Any</option>
                @foreach (App\Enums\Role::cases() as $roleOption)
                    <option value="{{ $roleOption->slug() }}" @selected($role === $roleOption->slug())>{{ $roleOption->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500" for="tier">Tier</label>
            <select id="tier" name="tier" class="mt-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-accent focus:outline-none">
                <option value="">Any</option>
                <option value="premium" @selected($tier === 'premium')>Premium</option>
                <option value="general" @selected($tier === 'general')>General</option>
            </select>
        </div>

        <button type="submit" class="cursor-pointer rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:opacity-90">
            Filter
        </button>

        @if ($search !== '' || $role || $tier)
            <a href="{{ route('admin.users.index') }}" class="cursor-pointer text-sm text-gray-500 hover:text-gray-700">
                Clear
            </a>
        @endif
    </form>

    <div class="animate-fade-in-up animation-delay-75 mt-6 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead>
                <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Tier</th>
                    <th class="px-6 py-3">Credits</th>
                    <th class="px-6 py-3">Next expiry</th>
                    <th class="px-6 py-3">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $listedUser)
                    @php
                        $available = (int) ($listedUser->available_credits ?? 0);
                        $isPremium = $available > 0;
                    @endphp
                    <tr>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.show', $listedUser) }}" class="cursor-pointer font-medium text-accent hover:underline">
                                {{ $listedUser->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $listedUser->email ?? $listedUser->mobile }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                {{ $listedUser->role->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $isPremium ? 'bg-accent/10 text-accent' : 'bg-gray-100 text-gray-700' }}">
                                {{ $isPremium ? 'Premium' : 'General' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $available }}</td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $listedUser->next_expiry ? \Illuminate\Support\Carbon::parse($listedUser->next_expiry)->format('M j, Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $listedUser->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">No users match.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
@endsection
