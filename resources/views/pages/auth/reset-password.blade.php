<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Choose a new password')"
            :description="__('Set a fresh password for your deliBEE account and get back to the hive.')"
        />

        <x-auth-session-status class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-center text-sm text-leaf dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <div>
                <label for="email" class="ui-label">{{ __('Email') }}</label>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email', request('email')) }}"
                       required
                       autocomplete="email"
                       class="ui-field mt-1.5"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="ui-label">{{ __('New password') }}</label>
                <div class="relative mt-1.5">
                    <input id="password"
                           name="password"
                           :type="show ? 'text' : 'password'"
                           required
                           autocomplete="new-password"
                           placeholder="At least 8 characters"
                           passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                           class="ui-field pe-16"
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>

                    <button type="button"
                            @click="show = !show"
                            :aria-pressed="show.toString()"
                            aria-label="Show or hide password"
                            class="absolute inset-y-1 end-1 rounded-lg px-3 text-xs font-semibold text-honey-deep transition hover:bg-hero dark:text-honey">
                        <span x-text="show ? 'Hide' : 'Show'">Show</span>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="ui-label">{{ __('Confirm password') }}</label>
                <input id="password_confirmation"
                       name="password_confirmation"
                       type="password"
                       required
                       autocomplete="new-password"
                       placeholder="Repeat your new password"
                       class="ui-field mt-1.5">
            </div>

            <button type="submit" class="ui-btn w-full" data-test="reset-password-button">
                {{ __('Reset password') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
