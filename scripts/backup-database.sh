#!/usr/bin/env bash
#
# Create a verified PostgreSQL custom-format backup of the deliBEE database.
#
# Reads DB_* from the project's .env, writes a timestamped dump into
# storage/backups, verifies it with pg_restore --list, and removes dumps older
# than --keep-days. Suitable for cron:
#
#   0 21 * * * cd /path/to/Delibee && ./scripts/backup-database.sh >> storage/logs/backup.log 2>&1
#
# Usage: ./scripts/backup-database.sh [options]
#   --output-dir DIR    Where to write dumps (default: storage/backups)
#   --keep-days N       Delete dumps older than N days (default: 14)
#   --pg-dump PATH      pg_dump binary (default: pg_dump from PATH or $PG_DUMP)
#   --pg-restore PATH   pg_restore binary (default: pg_restore from PATH or $PG_RESTORE)
#   --help              Show this help

set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
project_root="$(cd "$script_dir/.." && pwd)"

output_dir="$project_root/storage/backups"
keep_days=14
pg_dump_bin="${PG_DUMP:-pg_dump}"
pg_restore_bin="${PG_RESTORE:-pg_restore}"

usage() {
    cat <<'EOF'
Create a verified PostgreSQL custom-format backup of the deliBEE database.

Reads DB_* from the project's .env, writes a timestamped dump into
storage/backups, verifies it with pg_restore --list, and removes dumps older
than --keep-days. Suitable for cron:

  0 21 * * * cd /path/to/Delibee && ./scripts/backup-database.sh >> storage/logs/backup.log 2>&1

Usage: ./scripts/backup-database.sh [options]
  --output-dir DIR    Where to write dumps (default: storage/backups)
  --keep-days N       Delete dumps older than N days (default: 14)
  --pg-dump PATH      pg_dump binary (default: pg_dump from PATH or $PG_DUMP)
  --pg-restore PATH   pg_restore binary (default: pg_restore from PATH or $PG_RESTORE)
  --help              Show this help
EOF
}

while [ $# -gt 0 ]; do
    case "$1" in
        --output-dir)
            output_dir="${2:?--output-dir requires a value}"
            shift 2
            ;;
        --keep-days)
            keep_days="${2:?--keep-days requires a value}"
            shift 2
            ;;
        --pg-dump)
            pg_dump_bin="${2:?--pg-dump requires a value}"
            shift 2
            ;;
        --pg-restore)
            pg_restore_bin="${2:?--pg-restore requires a value}"
            shift 2
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 2
            ;;
    esac
done

case "$keep_days" in
    ''|*[!0-9]*|0)
        echo "--keep-days must be a positive integer." >&2
        exit 2
        ;;
esac

env_file="$project_root/.env"

if [ ! -f "$env_file" ]; then
    echo "No .env file found at $env_file" >&2
    exit 1
fi

env_value() {
    local key="$1" line
    line=$(grep -E "^[[:space:]]*${key}[[:space:]]*=" "$env_file" | tail -n 1) || true

    if [ -z "$line" ]; then
        return 1
    fi

    printf '%s' "${line#*=}" \
        | tr -d '\r' \
        | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' \
              -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
}

for key in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
    if ! value=$(env_value "$key") || [ -z "$value" ]; then
        echo "$key is missing from $env_file" >&2
        exit 1
    fi
    printf -v "db_${key#DB_}" '%s' "$value"
done

for binary in "$pg_dump_bin" "$pg_restore_bin"; do
    if ! command -v "$binary" >/dev/null 2>&1; then
        echo "Command not found: $binary" >&2
        exit 1
    fi
done

mkdir -p "$output_dir"

timestamp="$(date +%Y%m%d-%H%M%S)"
file="$output_dir/delibee-$timestamp.dump"

PGPASSWORD="$db_PASSWORD" "$pg_dump_bin" \
    --host "$db_HOST" \
    --port "$db_PORT" \
    --username "$db_USERNAME" \
    --format=custom \
    --file="$file" \
    "$db_DATABASE"

if ! "$pg_restore_bin" --list "$file" >/dev/null 2>&1; then
    rm -f "$file"
    echo "Backup failed verification: pg_restore could not read the archive." >&2
    exit 1
fi

removed=0
while IFS= read -r old_dump; do
    [ -n "$old_dump" ] || continue
    rm -f -- "$old_dump"
    removed=$((removed + 1))
done < <(find "$output_dir" -maxdepth 1 -name 'delibee-*.dump' -type f -mtime +"$keep_days")

size="$(du -h "$file" | cut -f1)"
echo "Backup written: $file ($size)"
echo "Removed $removed backup(s) older than $keep_days day(s)."
