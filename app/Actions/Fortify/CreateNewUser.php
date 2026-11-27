<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $countries = config('countries', []);

        Validator::make($input, [
            ...$this->profileRules(),
            'username' => ['nullable', 'string', 'min:3', 'max:30', 'alpha_dash', 'unique:users,username'],
            'phone_country' => ['required', 'string', 'size:2', Rule::in(array_keys($countries))],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9][0-9\s().-]{5,19}$/'],
            'password' => ['min:8', ...$this->passwordRules()],
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value)) {
                    return;
                }

                $domain = Str::lower(Str::after($value, '@'));

                if (! in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
                    return;
                }

                $local = Str::lower(Str::before($value, '@'));
                $valid = Str::length($local) >= 6
                    && Str::length($local) <= 30
                    && preg_match('/^[a-z0-9.]+$/', $local) === 1
                    && ! Str::startsWith($local, '.')
                    && ! Str::endsWith($local, '.')
                    && ! Str::contains($local, '..');

                if (! $valid) {
                    $fail('Gmail addresses must be 6–30 characters and use only letters, numbers and periods (no period at the start or end, and no two in a row).');
                }
            },
        ])->validate();

        $country = Str::upper((string) $input['phone_country']);
        $dial = (string) ($countries[$country]['dial'] ?? '');
        $digits = preg_replace('/\D+/', '', (string) $input['phone']) ?? '';
        $phone = '+'.$dial.ltrim($digits, '0');

        $user = User::create([
            'name' => $input['name'],
            'username' => filled($input['username'] ?? null) ? Str::lower((string) $input['username']) : null,
            'email' => Str::lower($input['email']),
            'password' => $input['password'],
        ]);

        $user->profile()->create([
            'phone' => $phone,
            'phone_country' => $country,
        ]);

        $user->assignRole(Role::findOrCreate('customer', 'web'));

        return $user;
    }
}
