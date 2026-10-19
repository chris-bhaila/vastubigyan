<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Welcome to {{ config('app.name') }}</title>
    </head>
    <body style="margin:0; padding:0; background-color:#F4F6F3; font-family:'Segoe UI', Helvetica, Arial, sans-serif;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F6F3; padding:32px 16px;">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#FFFFFF; border-radius:16px; border:1px solid #DCE3DF; overflow:hidden;">
                        {{-- Header --}}
                        <tr>
                            <td align="center" style="background-color:#0E4A43; padding:32px 24px;">
                                <img src="{{ asset('images/logo-mark.png') }}" width="56" height="56" alt="{{ config('app.name') }}" style="display:block; border-radius:12px;">
                            </td>
                        </tr>

                        {{-- Body --}}
                        <tr>
                            <td style="padding:40px 32px 24px;">
                                <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#D9822B;">
                                    Welcome
                                </p>
                                <h1 style="margin:0 0 20px; font-size:26px; line-height:1.3; font-weight:700; color:#10231F;">
                                    Namaste, {{ $user->name }}!
                                </h1>

                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#4A5C57;">
                                    Thanks for joining {{ config('app.name') }}. Your account is ready — you can now explore Vastu analysis for your home or office, right from your dashboard.
                                </p>

                                <p style="margin:0 0 28px; font-size:15px; line-height:1.6; color:#4A5C57;">
                                    Here's what you can do next:
                                </p>

                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                                    <tr>
                                        <td style="padding:0 0 14px; font-size:15px; line-height:1.5; color:#10231F;">
                                            <strong>&#10003;</strong>&nbsp; Use the free 16-zone Vastu Compass to get your first analysis
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 14px; font-size:15px; line-height:1.5; color:#10231F;">
                                            <strong>&#10003;</strong>&nbsp; Pin rooms and objects to get a tailored Vastu report
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0; font-size:15px; line-height:1.5; color:#10231F;">
                                            <strong>&#10003;</strong>&nbsp; Upgrade anytime for detailed reports and advanced remedies
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="border-radius:10px; background-color:#D9822B;">
                                            <a href="{{ route('dashboard') }}" style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#10231F; text-decoration:none;">
                                                Go to your dashboard
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        {{-- Footer --}}
                        <tr>
                            <td style="padding:24px 32px 32px; border-top:1px solid #DCE3DF;">
                                <img src="{{ asset('images/logo.png') }}" width="100" alt="{{ config('app.name') }}" style="display:block; margin-bottom:12px;">
                                <p style="margin:0; font-size:12px; line-height:1.6; color:#4A5C57;">
                                    You're receiving this email because you created a {{ config('app.name') }} account{{ $user->email ? " with {$user->email}" : '' }}.
                                    If this wasn't you, please contact us.
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
