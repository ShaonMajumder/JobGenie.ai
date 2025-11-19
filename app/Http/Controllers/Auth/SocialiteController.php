<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $driver = Socialite::driver('google');

        if (config('services.google.redirect')) {
            $driver->redirectUrl(config('services.google.redirect'));
        }

        return $driver->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $driver = Socialite::driver('google');

            if (config('services.google.redirect')) {
                $driver->redirectUrl(config('services.google.redirect'));
            }

            $googleUser = $driver->stateless()->user();
        } catch (\Throwable $exception) {
            Log::warning('Google OAuth failed', [
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('login')->withErrors([
                'google' => 'Unable to authenticate with Google.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user && $googleUser->getEmail()) {
            $user = User::where('email', $googleUser->getEmail())->first();
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail() ?: sprintf('google_%s@example.com', Str::uuid()),
                'password' => bcrypt(Str::random(32)),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->route('dashboard')->with('status', 'Logged in with Google.');
    }
}
