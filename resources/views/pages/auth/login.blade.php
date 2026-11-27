<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Welcome back to the hive')"
            :description="__('Log in to track orders, chat with sellers and keep your Honey Points buzzing.')"
        />

        <x-auth-session-status class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-center text-sm text-leaf dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="ui-label">{{ __('Email or Username') }}</label>
                <input id="email"
                       name="email"
                       type="text"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="you@example.com or your username"
                       class="ui-field mt-1.5"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="ui-label">{{ __('Password') }}</label>
                <div class="relative mt-1.5">
                    <input id="password"
                           name="password"
                           :type="show ? 'text' : 'password'"
                           required
                           autocomplete="current-password"
                           placeholder="Your password"
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

            <div class="flex flex-wrap items-center justify-between gap-3 py-1">
                <label for="remember" class="inline-flex min-h-9 items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input id="remember" name="remember" type="checkbox" value="1"
                           @checked(old('remember'))
                           class="size-4 rounded border-ink/60 accent-[var(--color-honey)] focus:ring-honey">
                    {{ __('Remember me') }}
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey"
                       wire:navigate>
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="ui-btn w-full" data-test="login-button">
                {{ __('Log in') }}
            </button>
        </form>

        <x-auth.social-buttons />

        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <a href="{{ route('register') }}" class="font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                {{ __('Sign up') }}
            </a>
        </p>
    </div>
</x-layouts::auth>
