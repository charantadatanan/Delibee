<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Forgot password?')"
            :description="__('Enter the email linked to your deliBEE account. If it is registered, we will send a secure reset link.')"
        />

        <x-auth-session-status class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-center text-sm text-leaf dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="ui-label">{{ __('Email address') }}</label>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="email"
                       placeholder="you@example.com"
                       class="ui-field mt-1.5"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="ui-btn w-full" data-test="email-password-reset-link-button">
                {{ __('Request reset link') }}
            </button>
        </form>

        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <a href="{{ route('login') }}" class="font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                {{ __('Back to login') }}
            </a>
        </p>
    </div>
</x-layouts::auth>
