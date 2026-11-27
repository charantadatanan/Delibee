# Laravel Cloud deployment

The production site is hosted on Laravel Cloud:

**https://delibee-production-6l26tu.laravel.cloud**

## Infrastructure

| Item | Value |
|---|---|
| Application / environment | `delibee` / `production` |
| Region | Singapore (`ap-southeast-1`) |
| Runtime | PHP 8.5, Node 24 |
| Compute | App instance `flex-512mb`, 1 replica, hibernation enabled |
| Database | Laravel Serverless Postgres `delibee-db`, schema `production` |
| Object storage | Public bucket `delibee-public`, disk name `public`, default disk |
| Scheduler | Enabled on the App instance |
| Queue | Database driver; the app currently dispatches no queued jobs |

Sessions, cache and queues use the Postgres database. The object-storage bucket is the default
disk, so `Storage::disk()` and `Storage::disk('public')` both resolve to Cloud storage in
production. Local uploads use the `public` disk and `storage:link` as before.

## Build and deploy

Build command:

```sh
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
  && npm ci --audit false \
  && npm run build \
  && php artisan optimize
```

Deploy command:

```sh
php artisan migrate --force && php artisan db:seed --force
```

`db:seed` is idempotent for roles and categories; `DatabaseSeeder` deliberately skips demo
accounts when `APP_ENV=production`.

Push-to-deploy is enabled for `main`. A normal deploy is:

```sh
./vendor/bin/cloud deploy delibee production -n
./vendor/bin/cloud deploy:monitor -n
```

## Environment variables

Cloud injects `APP_KEY` and the Postgres and bucket connection values. The following are set
explicitly:

```ini
APP_NAME=deliBEE
APP_ENV=production
APP_DEBUG=false
APP_URL=https://delibee-production-6l26tu.laravel.cloud
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
DB_CONNECTION=pgsql
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
LOG_CHANNEL=stderr
LOG_LEVEL=info
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@delibee.example
MAIL_FROM_NAME=deliBEE
VITE_APP_NAME=deliBEE
BCRYPT_ROUNDS=12
```

## Email

Resend is not attached yet, so `MAIL_MAILER=log`. At `LOG_LEVEL=info` the log mailer's debug
output is filtered, so verification codes and reset links are neither delivered nor retained.
For temporary private testing only, set `LOG_LEVEL=debug`, trigger a resend, read the code from
the Cloud logs, and then restore `LOG_LEVEL=info`. To enable real email:

1. Connect Resend and verify a sending domain in the Laravel Cloud dashboard.
2. Add the mail package: `composer require resend/resend-php`.
3. Attach the sender:

   ```sh
   ./vendor/bin/cloud resend:attach {environment} --from-address=hello@your-domain --from-name=deliBEE -n
   ```

4. Redeploy and verify a registration end to end.

## Admin access

An initial verified admin account was created for the deployment. Change its password immediately
at `/settings/security` after the first login. Create a normal customer account for day-to-day use
rather than sharing the admin credentials.

Registration uses the production password policy (12+ characters, mixed case, number and symbol,
not known in a breach).

## Scheduled work

The App instance scheduler runs `php artisan schedule:run` every minute. The application schedules:

- `bee:rank --period=month` on the first day of each month at 02:15.
- `bee:rank --period=year` on 1 January at 03:30.
- `delibee:prune` daily at 03:05.

## First-deploy checklist

- [x] App and production environment created
- [x] Serverless Postgres attached
- [x] Public bucket attached as the default `public` disk
- [x] Production environment variables set
- [x] Build and deploy commands set
- [x] Scheduler enabled
- [x] Initial deployment succeeded
- [x] `/up`, marketplace, auth and Hive Guard checked
- [x] Object-storage write/read/delete checked
- [ ] Resend connected and a verified sending domain attached
- [ ] Custom domain and DNS configured
