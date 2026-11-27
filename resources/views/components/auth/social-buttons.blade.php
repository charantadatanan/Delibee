<div class="space-y-3">
    <div class="flex items-center gap-3" aria-hidden="true">
        <span class="h-px flex-1 bg-ink/15 dark:bg-zinc-800"></span>
        <span class="text-[11px] font-medium uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">{{ __('Or continue with') }}</span>
        <span class="h-px flex-1 bg-ink/15 dark:bg-zinc-800"></span>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <a href="{{ route('social.redirect', 'google') }}"
           class="ui-btn ui-btn-ghost h-12 px-0"
           aria-label="Continue with Google">
            <img src="{{ asset('images/auth/google.png') }}" alt="" class="size-6 object-contain" width="25" height="25">
        </a>

        <a href="{{ route('social.redirect', 'facebook') }}"
           class="ui-btn ui-btn-ghost h-12 px-0"
           aria-label="Continue with Facebook">
            <img src="{{ asset('images/auth/facebook.png') }}" alt="" class="size-6 object-contain" width="26" height="26">
        </a>

        <button type="button"
                class="ui-btn ui-btn-ghost h-12 cursor-not-allowed px-0 opacity-50"
                disabled
                aria-label="Apple sign-in requires a paid Apple developer account"
                title="Apple sign-in needs a paid Apple developer setup.">
            <img src="{{ asset('images/auth/apple.png') }}" alt="" class="size-6 object-contain" width="29" height="29">
        </button>
    </div>

    @error('social')
        <p class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-center text-xs font-medium text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
            {{ $message }}
        </p>
    @enderror
</div>
