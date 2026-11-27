<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area. Please confirm your password before continuing.')"
        />

        <x-auth-session-status class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-center text-sm text-leaf dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
            @csrf

            <div x-data="{ show: false }">
                <label for="password" class="ui-label">{{ __('Password') }}</label>
                <div class="relative mt-1.5">
                    <input id="password"
                           name="password"
                           :type="show ? 'text' : 'password'"
                           required
                           autocomplete="current-password"
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

            <button type="submit" class="ui-btn w-full" data-test="confirm-password-button">
                {{ __('Confirm') }}
            </button>
        </form>
    </div>
</x-layouts::auth>
