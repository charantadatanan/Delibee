<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailVerificationCodeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $expiresAt = $user->email_verification_code_expires_at;
        $hash = $user->email_verification_code_hash;

        if ($hash === null || $expiresAt === null || $expiresAt->isPast() || ! Hash::check($validated['code'], $hash)) {
            throw ValidationException::withMessages([
                'code' => 'That verification code is invalid or has expired. Request a new code and try again.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $user->forceFill([
            'email_verification_code_hash' => null,
            'email_verification_code_expires_at' => null,
        ])->save();

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
