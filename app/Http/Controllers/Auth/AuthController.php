<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class AuthController extends Controller
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            activity('auth')
                ->withProperties(['mobile' => $credentials['mobile'], 'ip' => $request->ip()])
                ->log('Failed login attempt');

            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        activity('auth')
            ->causedBy(Auth::user())
            ->withProperties(['method' => 'mobile', 'ip' => $request->ip()])
            ->log('User logged in');

        return redirect()->intended(route(Auth::user()->dashboardRouteName()));
    }

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()
                ->route('login')
                ->withErrors(['google' => 'Google sign-in failed. Please try again.']);
        }

        $user = $this->users->firstOrCreateFromGoogle([
            'name' => $googleUser->getName() ?? $googleUser->getNickname(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        Auth::login($user, remember: true);

        activity('auth')
            ->causedBy($user)
            ->withProperties(['method' => 'google', 'ip' => request()->ip()])
            ->log('User logged in');

        return redirect()->intended(route($user->dashboardRouteName()));
    }

    public function logout(): RedirectResponse
    {
        activity('auth')
            ->causedBy(Auth::user())
            ->withProperties(['ip' => request()->ip()])
            ->log('User logged out');

        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
