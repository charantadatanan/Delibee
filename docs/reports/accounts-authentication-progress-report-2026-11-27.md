# deliBEE — Accounts & Authentication Progress Report

**Project:** deliBEE multi-vendor marketplace
**Repository:** https://github.com/charantadatanan/Delibee
**Report date:** 27 November 2026
**Scope:** Accounts & Authentication
**Production:** https://delibee-production-6l26tu.laravel.cloud
**Status:** Authentication live; outbound auth email blocked

---

## Progress

**Fortify authentication.**
Laravel Fortify is the authentication foundation. `config/fortify.php` enables registration, password resets and email verification, with `/dashboard` as the post-auth home. `app/Providers/FortifyServiceProvider.php` binds the login, registration, verification, password-confirmation and password-reset views, and registers the create-user and reset-password actions.

**Email/password registration.**
`app/Actions/Fortify/CreateNewUser.php` validates name, email, optional username, country, phone and password confirmation, creates the user and the related profile row. The phone is normalised to `+<dial><national digits>` using the selected country. `app/Concerns/PasswordValidationRules.php` supplies the policy, with production defaults set in `app/Providers/AppServiceProvider.php` (12 characters, mixed case, letters, numbers, symbols and breach check).

**245-country phone picker.**
`config/countries.php` contains 245 country and dial-code entries. `resources/views/pages/auth/register.blade.php` renders the country select and phone field, defaults to PH, and shows validation errors inline.

**Gmail address rules.**
`CreateNewUser` applies the RAFS rule to `gmail.com` and `googlemail.com` addresses: the local part must be 6–30 characters using letters, numbers and periods, with no leading period, trailing period or consecutive periods. Other domains use Laravel's normal email validation.

**Six-digit email OTP and signed link.**
`app/Models/User.php::sendEmailVerificationNotification()` generates a random six-digit code, stores only a one-way digest with a 10-minute expiry, and builds a one-hour signed verification URL. `app/Notifications/VerifyEmailCode.php` sends the code and a "verify with a link" action. `app/Http/Controllers/Auth/EmailVerificationCodeController.php` validates the code and marks the email verified; `routes/web.php` exposes `POST /email/verify-code` behind auth and a 6/minute throttle. The supporting columns are added by `database/migrations/2026_11_27_085804_add_rafs_auth_fields_to_users_and_profiles.php`.

**Email-or-username login.**
`FortifyServiceProvider::configureActions()` accepts the submitted login value, detects an email with `FILTER_VALIDATE_EMAIL`, and otherwise looks up the lower-cased username. `resources/views/pages/auth/login.blade.php` labels the field "Email or Username" and accepts either.

**15-minute login throttling.**
The `login` limiter in `app/Providers/FortifyServiceProvider.php` allows 5 attempts per 15 minutes, keyed by login value and IP. `config/fortify.php` points Fortify at that limiter.

**30-minute password-reset links.**
`config/auth.php` sets the `users` broker expiry to 30 minutes with a 60-second request throttle. `app/Actions/Fortify/ResetUserPassword.php` validates and stores the new password; the request and reset screens are `resources/views/pages/auth/forgot-password.blade.php` and `resources/views/pages/auth/reset-password.blade.php`.

**Username profiles.**
The `users.username` column is nullable, unique and limited to 30 characters. `CreateNewUser` stores it lower-cased; login looks it up the same way. Users can edit username, bio and phone at `/settings/profile` (`resources/views/pages/settings/profile.blade.php`), with uniqueness enforced on update.

**Social OAuth scaffolding.**
`app/Http/Controllers/Auth/SocialAuthController.php` implements Google and Facebook redirect and callback flows with Socialite, links identities through `app/Models/SocialIdentity.php`, and stores them in the `social_identities` table (`database/migrations/2026_11_27_090932_create_social_identities_table.php`). Routes are `auth/{provider}/redirect` and `auth/{provider}/callback` in `routes/web.php`. The UI is intentionally marked under development: `resources/views/components/auth/social-buttons.blade.php` disables Google, Facebook and Apple with an explicit note.

---

## Final output

- Live authentication works on the Laravel Cloud production environment: `/login` and `/register` both return HTTP 200.
- Production holds **25 users**.
- The account audit found **all 11 local accounts represented** in production; no local email is missing.
- **7 demo accounts** are seeded by `database/seeders/DemoSeeder.php`: one admin, one customer and five seller owners.
- **4 migrated test accounts** and **5 migrated sellers** are present in production.
- Email OTP generation was verified: a six-digit code is produced, only its digest is persisted with a 10-minute expiry, and the notification carries both the code and the signed link.
- Social buttons render on login and registration as disabled "under development" controls; the OAuth routes remain registered for later activation.

| Account group | Count |
|---|---:|
| Production users | 25 |
| Local accounts represented | 11 / 11 |
| Demo accounts | 7 |
| Migrated test accounts | 4 |
| Migrated sellers | 5 |

---

## Verification

- **Automated tests.** `php artisan test tests/Feature/Auth tests/Feature/Settings/ProfileUpdateTest.php` ran on 27 November 2026: **25 tests, 24 passed, 1 skipped, 65 assertions**. The skip is the two-factor test, since 2FA is disabled in this build. Coverage includes `tests/Feature/Auth/AuthenticationTest.php` (email login, username login, invalid password, logout), `RegistrationTest.php`, `EmailVerificationTest.php` (signed link, six-digit code, expired and invalid codes), `PasswordResetTest.php` and `tests/Feature/Settings/ProfileUpdateTest.php`.
- **Live login tested.** Login was exercised against the production deployment; both auth pages respond HTTP 200 and the email-or-username field is present.
- **Account audit.** 25 production users were reconciled against the 11 local accounts. All 11 are represented and no local email is missing. The audit also accounts for the 7 seeded demo accounts, 4 migrated test accounts and 5 migrated sellers.
- **OTP generation.** The generation path in `app/Models/User.php` and the mail notification in `app/Notifications/VerifyEmailCode.php` were checked, and the verification suite covers acceptance, expiry and invalid-code rejection.

---

## Open item

**OTP and password-reset email delivery is blocked.** Production has no verified Resend sending domain and no SMTP provider attached. `docs/guides/laravel-cloud.md` sets `MAIL_MAILER=log` and `LOG_LEVEL=info`; the log mailer writes at debug level, which this level filters out, so verification codes and reset links are neither delivered nor retained. New users can complete registration but cannot receive the OTP or a reset link by email.

The application side is ready: the OTP and reset flows are implemented, `resend/resend-php` `^1.19` is installed in `composer.json` (commit `d6c2f54`), and `config/mail.php` already defines the `resend` transport. Closing the item requires connecting Resend, verifying a sending domain, attaching the sender in Laravel Cloud, setting `MAIL_MAILER=resend` with a verified `MAIL_FROM_ADDRESS`, redeploying, and registering plus resetting a password end to end. The Laravel Cloud checklist still has "Resend connected and a verified sending domain attached" unchecked.

## Update — 28 November 2026

New registrations now receive the `customer` role in `app/Actions/Fortify/CreateNewUser.php`, with coverage in `tests/Feature/Auth/RegistrationTest.php`. The eight existing visitor accounts that had no role were assigned `customer` in production. Current role counts: **2 admins, 10 sellers, 13 customers, 0 roleless**. Five sellers own shops; the five migrated local seller accounts reach seller onboarding because their shops already exist under the demo seller accounts. The full suite now reports **123 tests, 122 passed, 1 skipped, 316 assertions**.
