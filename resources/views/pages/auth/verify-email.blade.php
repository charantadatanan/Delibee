<x-layouts::auth :title="__('Email verification')">
    <div class="flex flex-col gap-6 text-center">
        <span class="mx-auto grid size-16 place-items-center rounded-full border border-ink/60 bg-honey">
            <flux:icon name="envelope" class="size-8 text-ink" />
        </span>

        <x-auth-header
            :title="__('Verify your email')"
            :description="__('Click the link we just emailed you to finish setting up your deliBEE account.')"
        />

        @if (session('status') == 'verification-link-sent')
            <p class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-sm font-medium text-leaf dark:text-emerald-300">
                {{ __('A new code and verification link have been sent to your email address.') }}
            </p>
        @endif

        @if (session('status') == 'verification-code-verified')
            <p class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-sm font-medium text-leaf dark:text-emerald-300">
                {{ __('Email verified. Welcome to the hive.') }}
            </p>
        @endif

        <form method="POST" action="{{ route('verification.code') }}" class="space-y-4 text-left">
            @csrf

            <div>
                <label for="code" class="ui-label">{{ __('Six-digit code') }}</label>
                <input id="code"
                       name="code"
                       type="text"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       autocomplete="one-time-code"
                       required
                       class="ui-field mt-1.5 text-center font-display text-2xl tracking-[0.35em]"
                       aria-describedby="code-help"
                       value="{{ old('code') }}"
                       @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
                <p id="code-help" class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                    Enter the code from the email, or use the verification link inside it.
                </p>
                @error('code')
                    <p id="code-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="ui-btn w-full">
                {{ __('Verify email') }}
            </button>
        </form>

        <div class="flex flex-col gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-ghost w-full">
                    {{ __('Resend code and link') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-400" data-test="logout-button">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </div>
</x-layouts::auth>
