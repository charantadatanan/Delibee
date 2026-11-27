<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialIdentity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const PROVIDERS = [
        'google' => 'Google',
        'facebook' => 'Facebook',
    ];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(array_key_exists($provider, self::PROVIDERS), 404);

        if (! $this->isConfigured($provider)) {
            return $this->notConfigured($provider);
        }

        $target = Socialite::driver($provider)->redirect()->getTargetUrl();

        return redirect()->away($target);
    }

    public function callback(string $provider): RedirectResponse
    {
        abort_unless(array_key_exists($provider, self::PROVIDERS), 404);

        if (! $this->isConfigured($provider)) {
            return $this->notConfigured($provider);
        }

        try {
            $social = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'social' => self::PROVIDERS[$provider].' sign-in could not be completed. Please try again.',
            ]);
        }

        $email = Str::lower((string) $social->getEmail());
        $subject = (string) $social->getId();

        if ($email === '' || $subject === '') {
            return redirect()->route('login')->withErrors([
                'social' => self::PROVIDERS[$provider].' did not return an email address.',
            ]);
        }

        $identity = SocialIdentity::query()
            ->where('provider', $provider)
            ->where('subject', $subject)
            ->first();

        $user = $identity?->user;

        if ($user === null) {
            if ($identity !== null) {
                $identity->delete();
            }

            $user = User::query()->where('email', $email)->first();

            if ($user === null) {
                $user = User::create([
                    'name' => $social->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'password' => Str::random(40),
                    'email_verified_at' => now(),
                ]);

                $user->profile()->create();
            } elseif (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            SocialIdentity::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'subject' => $subject,
            ]);
        }

        Auth::login($user, true);
        session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function isConfigured(string $provider): bool
    {
        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"));
    }

    private function notConfigured(string $provider): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'social' => self::PROVIDERS[$provider].' sign-in is not configured yet. Add the provider credentials to enable it.',
        ]);
    }
}
