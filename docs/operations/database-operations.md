# Database operations

This runbook covers the operational side of the deliBEE PostgreSQL database: backups, off-site copies, restore drills, retention, point-in-time recovery, and network hardening.

## 1. Backups

Two equivalent scripts produce verified PostgreSQL custom-format dumps:

| Platform | Script |
|---|---|
| Windows | `scripts/backup/backup-database.ps1` |
| Linux/macOS | `scripts/backup/backup-database.sh` |

Both read `DB_*` from `.env`, write a timestamped `delibee-YYYYMMDD-HHMMSS.dump` to `storage/backups`, verify it with `pg_restore --list`, and prune dumps older than the retention window.

### Off-site copies

A backup that only exists on the same disk is not a backup. Both scripts support a second destination:

```powershell
# Windows — any folder that syncs off the machine (OneDrive, Dropbox, network share, USB)
powershell -ExecutionPolicy Bypass -File scripts\backup\backup-database.ps1 `
  -OffsiteDir "$env:USERPROFILE\OneDrive\delibee-backups"
```

```bash
# Linux/macOS — mounted NAS, object-storage sync folder, etc.
./scripts/backup/backup-database.sh --offsite-dir /mnt/backups/delibee
```

The copy is verified byte-for-byte (SHA-256 on Windows, `cmp` on Linux) and pruned with the same `-KeepDays`/`--keep-days` window. The destination can also be set once with the `BACKUP_OFFSITE_DIR` environment variable.

### Scheduling

Windows:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\backup\register-backup-task.ps1 `
  -OffsiteDir "$env:USERPROFILE\OneDrive\delibee-backups"
```

Linux cron:

```cron
0 21 * * * cd /srv/delibee && OFFSITE_BACKUP_DIR=/mnt/backups/delibee ./scripts/backup/backup-database.sh >> storage/logs/backup.log 2>&1
```

### Restore drill

Run this periodically — a dump is only proven when it restores:

```powershell
# 1. Create a scratch database owned by the application role
createdb -h 127.0.0.1 -p 5432 -U postgres -O delibee delibee_restore_check

# 2. Restore the latest verified dump (stop on the first error)
pg_restore -h 127.0.0.1 -p 5432 -U delibee -d delibee_restore_check -e storage\backups\delibee-YYYYMMDD-HHMMSS.dump

# 3. Compare table and row counts with the live database, then drop the scratch database
psql -h 127.0.0.1 -p 5432 -U postgres -d postgres -c "DROP DATABASE delibee_restore_check;"
```

The 12 November 2026 drill restored 28 tables and 21 migrations with matching row counts.

## 2. Retention

`php artisan delibee:prune` removes sessions idle for more than 7 days and failed jobs older than 30 days. It is scheduled daily at 03:05 in `routes/console.php`.

- Run the scheduler with `php artisan schedule:work` in development, or a cron/Task Scheduler entry in production.
- Override the windows with `--session-days` and `--failed-job-days`.
- `rank_snapshots` are historical records and are kept indefinitely.

## 3. Point-in-time recovery (production)

Nightly dumps recover to the last dump. PITR additionally keeps every WAL segment so you can recover to a specific moment. This requires a PostgreSQL restart and is intended for the production server, not the local development database.

### Enable WAL archiving

In `postgresql.conf` (or via `ALTER SYSTEM`):

```conf
wal_level = replica
archive_mode = on
archive_timeout = 60s

# Windows
archive_command = 'copy "%p" "D:\\pg_wal_archive\\%f"'

# Linux
archive_command = 'test ! -f /srv/pg_wal_archive/%f && cp %p /srv/pg_wal_archive/%f'
```

Restart PostgreSQL, then confirm:

```sql
SHOW archive_mode;
SHOW archive_command;
```

### Base backup

```bash
pg_basebackup -h 127.0.0.1 -p 5432 -U postgres -D /srv/backups/base -Ft -z -P
```

Store base backups and WAL archives on different storage from the primary database, and copy both off-site.

### Restore to a point in time

1. Stop PostgreSQL.
2. Restore the latest base backup into the data directory.
3. Create an empty `recovery.signal` file in the data directory.
4. Set `restore_command` to read from the WAL archive, and optionally `recovery_target_time`.
5. Start PostgreSQL; it replays WAL until the target, then promotes the server.

### Retention

Keep enough base backups and WAL segments to cover the agreed recovery window. `pg_archivecleanup` can remove WAL older than a chosen base backup; schedule it after verifying the base backup.

If PITR is required in production, a managed PostgreSQL service is usually the safer choice — it provides continuous backup, PITR and off-site storage without maintaining archive commands on the application server.

## 4. Network hardening

### Bind to loopback

The development server currently advertises `listen_addresses = '*'`; host-based rules still restrict logins to loopback. For defense in depth:

```sql
ALTER SYSTEM SET listen_addresses = 'localhost';
```

Restart PostgreSQL (Windows: `Restart-Service postgresql-x64-18` as Administrator; Linux: `systemctl restart postgresql`), then verify with `SHOW listen_addresses;`.

### SSL

Only needed if the database moves off the local machine:

```conf
ssl = on
ssl_cert_file = 'server.crt'
ssl_key_file = 'server.key'
```

Use `hostssl` entries in `pg_hba.conf`, restart PostgreSQL, and point Laravel at it with `DB_SSLMODE=require` (or `verify-full` with a trusted CA). Laravel reads `sslmode`, `sslcert`, `sslkey` and `sslrootcert` from the `pgsql` connection.

## 5. Verification checklist

- [ ] Dump created and verified with `pg_restore --list`.
- [ ] Off-site copy exists and matches the local file.
- [ ] Restore drill completes with matching table and row counts.
- [ ] Scheduler is running; `delibee:prune` and BeeRank jobs appear in `php artisan schedule:list`.
- [ ] Backup task/cron entry is enabled and its last run succeeded.
- [ ] WAL archiving (if enabled) has no failures in the PostgreSQL log.
- [ ] `listen_addresses` and `pg_hba.conf` match the intended exposure.
- [ ] Credentials live in `.env` or a secret store — never in git.

## 6. Security notes

- Never commit `.env`, dump files or certificates. `storage/backups` is gitignored.
- Use a dedicated backup database role with only the privileges it needs; avoid the superuser for scheduled dumps.
- Encrypt off-site backups if the destination is shared (for example, a password-protected archive or an encrypted cloud bucket).
