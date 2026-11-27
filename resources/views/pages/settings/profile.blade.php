<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $bio = '';
    public string $phone = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->username = Auth::user()->username ?? '';
        $this->email = Auth::user()->email;
        $this->bio = Auth::user()->profile?->bio ?? '';
        $this->phone = Auth::user()->profile?->phone ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            ...$this->profileRules($user->id),
            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'username' => filled($validated['username'] ?? null)
                ? strtolower((string) $validated['username'])
                : null,
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => ($validated['bio'] ?? '') !== '' ? $validated['bio'] : null,
                'phone' => ($validated['phone'] ?? '') !== '' ? $validated['phone'] : null,
            ],
        );

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Edit Profile')" :subheading="__('Update your name, contact details and bio')">
        <form wire:submit="updateProfileInformation" class="space-y-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-display text-xl font-semibold">Edit Profile</h2>
                <button type="submit" class="ui-btn ui-btn-sm" data-test="update-profile-button">
                    Save
                </button>
            </div>

            <div class="flex flex-col items-center gap-1 text-center">
                <span class="grid size-24 place-items-center overflow-hidden rounded-full border-2 border-ink bg-white font-display text-2xl font-bold text-honey-deep dark:border-zinc-700 dark:bg-zinc-950 dark:text-honey">
                    @if (auth()->user()->profile?->avatar_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->profile->avatar_path) }}" alt="" class="size-full object-cover">
                    @else
                        {{ auth()->user()->initials() }}
                    @endif
                </span>
                <span class="text-[10px] text-zinc-500 dark:text-zinc-400">Photo uploads are coming soon</span>
            </div>

            <div>
                <label for="profile-name" class="ui-label">{{ __('Full Name') }}</label>
                <input id="profile-name" wire:model="name" type="text" required autofocus autocomplete="name" class="ui-field mt-1.5">
                @error('name') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="profile-username" class="ui-label">{{ __('Username') }}</label>
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm text-zinc-400" aria-hidden="true">@</span>
                    <input id="profile-username" wire:model="username" type="text" autocomplete="username" placeholder="onekkk" class="ui-field ps-8">
                </div>
                <p class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">Use 3–30 letters, numbers, dashes or underscores. You can sign in with it.</p>
                @error('username') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="profile-email" class="ui-label">{{ __('Email Address') }}</label>
                <input id="profile-email" wire:model="email" type="email" required autocomplete="email" class="ui-field mt-1.5">
                @error('email') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-2 rounded-xl border border-ink/20 bg-white/60 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-950">
                        <p>{{ __('Your email address is unverified.') }}</p>
                        <button type="button" wire:click.prevent="resendVerificationNotification" class="mt-1 font-semibold text-honey-deep underline-offset-4 hover:underline dark:text-honey">
                            {{ __('Re-send the verification email.') }}
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-leaf dark:text-emerald-300">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <label for="profile-phone" class="ui-label">{{ __('Phone No.') }}</label>
                <input id="profile-phone" wire:model="phone" type="tel" autocomplete="tel" placeholder="+63 900 000 0000" class="ui-field mt-1.5">
                @error('phone') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="profile-bio" class="ui-label">{{ __('Bio') }}</label>
                <textarea id="profile-bio" wire:model="bio" rows="4" class="ui-field mt-1.5" placeholder="Tell the hive a little about yourself"></textarea>
                @error('bio') <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="ui-btn w-full" data-test="update-profile-button-bottom">
                {{ __('Save profile') }}
            </button>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-6 border-t border-ink/15 pt-5 dark:border-zinc-800">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>
