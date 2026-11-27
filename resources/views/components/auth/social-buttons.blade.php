<div class="space-y-3">
    <div class="flex items-center gap-3" aria-hidden="true">
        <span class="h-px flex-1 bg-ink/15 dark:bg-zinc-800"></span>
        <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">{{ __('Or continue with') }}</span>
        <span class="h-px flex-1 bg-ink/15 dark:bg-zinc-800"></span>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <button type="button"
                class="ui-btn ui-btn-ghost h-12 cursor-not-allowed px-0 opacity-55"
                disabled
                aria-disabled="true"
                aria-label="Google sign-in is under development"
                title="Google sign-in is under development.">
            <img src="{{ asset('images/auth/google.png') }}" alt="" class="size-6 object-contain" width="25" height="25">
        </button>

        <button type="button"
                class="ui-btn ui-btn-ghost h-12 cursor-not-allowed px-0 opacity-55"
                disabled
                aria-disabled="true"
                aria-label="Facebook sign-in is under development"
                title="Facebook sign-in is under development.">
            <img src="{{ asset('images/auth/facebook.png') }}" alt="" class="size-6 object-contain" width="26" height="26">
        </button>

        <button type="button"
                class="ui-btn ui-btn-ghost h-12 cursor-not-allowed px-0 opacity-55"
                disabled
                aria-disabled="true"
                aria-label="Apple sign-in is under development"
                title="Apple sign-in is under development.">
            <img src="{{ asset('images/auth/apple.png') }}" alt="" class="size-6 object-contain" width="29" height="29">
        </button>
    </div>

    <p class="text-center text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
        {{ __('Google, Facebook and Apple sign-in are under development.') }}
    </p>

    @error('social')
        <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-center text-xs font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </p>
    @enderror
</div>
