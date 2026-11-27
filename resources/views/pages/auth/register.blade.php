<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create your account')"
            :description="__('Join deliBEE and be part of a community that supports local businesses.')"
        />

        <x-auth-session-status class="rounded-xl border border-leaf/30 bg-leaf/10 px-4 py-3 text-center text-sm text-leaf dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="ui-label">{{ __('Full name') }}</label>
                <input id="name"
                       name="name"
                       type="text"
                       value="{{ old('name') }}"
                       required
                       autofocus
                       autocomplete="name"
                       placeholder="e.g. Unique Javier"
                       class="ui-field mt-1.5"
                       @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')
                    <p id="name-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="ui-label">{{ __('Email address') }}</label>
                <input id="email"
                       name="email"
                       type="email"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       placeholder="you@example.com"
                       class="ui-field mt-1.5"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-[8.5rem_1fr] gap-2">
                <div>
                    <label for="phone_country" class="ui-label">{{ __('Country') }}</label>
                    <select id="phone_country"
                            name="phone_country"
                            required
                            class="ui-field mt-1.5"
                            @error('phone_country') aria-invalid="true" aria-describedby="phone-country-error" @enderror>
                        @foreach ($countries as $code => $country)
                            <option value="{{ $code }}" @selected(old('phone_country', 'PH') === $code)>
                                {{ $country['name'] }} +{{ $country['dial'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('phone_country')
                        <p id="phone-country-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="ui-label">{{ __('Phone number') }}</label>
                    <input id="phone"
                           name="phone"
                           type="tel"
                           value="{{ old('phone') }}"
                           required
                           autocomplete="tel-national"
                           inputmode="tel"
                           placeholder="912 345 6789"
                           class="ui-field mt-1.5"
                           @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                    @error('phone')
                        <p id="phone-error" class="mt-1.5 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="ui-label">{{ __('Password') }}</label>
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
                       placeholder="Repeat your password"
                       class="ui-field mt-1.5">
            </div>

            <p class="text-xs leading-relaxed text-zinc-500 dark:text-zinc-400">
                By creating an account you agree to the deliBEE terms and privacy policy.
            </p>

            <button type="submit" class="ui-btn w-full" data-test="register-user-button">
                {{ __('Sign up') }}
            </button>
        </form>

        <x-auth.social-buttons />

        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey" wire:navigate>
                {{ __('Log in') }}
            </a>
        </p>
    </div>
</x-layouts::auth>
