# RAFS account authentication port

Ported from `Delibeee_RAFS_VERSION` into the Laravel/Fortify auth flow.

## Implemented

- **Register** — full name, email, 245-country phone picker, phone number, password and confirmation.
  Gmail addresses follow the RAFS 6–30 character rule; passwords keep Laravel's configured strength rules
  with a minimum of 8.
- **Login** — one field accepts email **or** username; 5 failed attempts are limited for 15 minutes.
- **Verify email** — a six-digit code (10-minute expiry) is sent alongside the signed verification link.
  The code can be entered on `/email/verify`; “Resend code and link” regenerates both.
- **Forgot / reset password** — reset links expire after 30 minutes.
- **Profile** — username is editable on `/settings/profile`; it is unique, stored lowercase, and can be
  used at login. Phone is stored on the existing `profiles` row.
- **Social auth** — Google and Facebook buttons use Laravel Socialite; Apple is intentionally disabled
  until a paid Apple developer account exists. Provider credentials are environment-only.

## Database changes

Migration `2026_11_27_085804_add_rafs_auth_fields_to_users_and_profiles` adds:

- `users.username` (nullable, unique)
- `users.email_verification_code_hash`
- `users.email_verification_code_expires_at`
- `profiles.phone_country`

Migration `2026_11_27_090932_create_social_identities_table` adds `social_identities` with a unique
`[provider, subject]` identity and a unique `[user_id, provider]` link.

## Social provider setup

1. Add the Google/Facebook client IDs and secrets to `.env`.
2. Set the provider callback URLs to:
   - `https://your-domain/auth/google/callback`
   - `https://your-domain/auth/facebook/callback`
3. Add the same URLs to the provider consoles.
4. Configure mail (`MAIL_*`) so verification codes and reset links are delivered.

Until credentials are added, clicking a social button returns to Login with a clear “not configured”
message instead of failing silently.

## Still to decide

- Per-country phone-number validation (the picker provides the country and dial code; the server
  currently validates digit shape only).
- RAFS's “unregistered email → Register prefill” redirect.
- A real seller/customer avatar upload flow.
- Apple sign-in and live provider verification.
