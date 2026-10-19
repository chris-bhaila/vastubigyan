Namaste, {{ $user->name }}!

Thanks for joining {{ config('app.name') }}. Your account is ready — you can now explore Vastu analysis for your home or office, right from your dashboard.

Here's what you can do next:
- Use the free 16-zone Vastu Compass to get your first analysis
- Pin rooms and objects to get a tailored Vastu report
- Upgrade anytime for detailed reports and advanced remedies

Go to your dashboard: {{ route('dashboard') }}

You're receiving this email because you created a {{ config('app.name') }} account{{ $user->email ? " with {$user->email}" : '' }}. If this wasn't you, please contact us.
