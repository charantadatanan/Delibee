#!/usr/bin/env bash
#
# Restore a deliBEE backup into a scratch database and verify it.
#
# Picks the latest dump in storage/backups (or --dump-file), creates a scratch
# database owned by the application role, restores it with pg_restore -e,
# compares table, migration and key row counts with the live database, then
# drops the scratch database.
#
# The admin password is only used to create and drop the scratch database.
# Pass it with --admin-password or set PG_ADMIN_PASSWORD.
#
# Usage: ./scripts/backup/verify-backup.sh [options]
#   --dump-file PATH       Dump to verify (default: newest in storage/backups)
#   --scratch-db NAME      Scratch database name (default: delibee_restore_check)
#   --admin-user USER      PostgreSQL admin role (default: postgres)
#   --admin-password PASS  Admin password (default: $PG_ADMIN_PASSWORD)
#   --pg-bin DIR           Directory containing createdb/pg_restore/psql/dropdb
#   --help                 Show this help

set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
project_root="$(cd "$script_dir/../.." && pwd)"

dump_file=""
scratch_db="delibee_restore_check"
admin_user="postgres"
admin_password="${PG_ADMIN_PASSWORD:-}"
pg_bin="${PG_BIN:-}"

usage() {
    sed -n '2,20p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'
}

while [ $# -gt 0 ]; do
    case "$1" in
        --dump-file)
            dump_file="${2:?--dump-file requires a value}"
            shift 2
            ;;
        --scratch-db)
            scratch_db="${2:?--scratch-db requires a value}"
            shift 2
            ;;
        --admin-user)
            admin_user="${2:?--admin-user requires a value}"
            shift 2
            ;;
        --admin-password)
            admin_password="${2:?--admin-password requires a value}"
            shift 2
            ;;
        --pg-bin)
            pg_bin="${2:?--pg-bin requires a value}"
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

if [ "$scratch_db" = "$db_DATABASE" ]; then
    echo "The scratch database must differ from the live database." >&2
    exit 1
fi

if [ -z "$admin_password" ]; then
    echo "Pass --admin-password or set PG_ADMIN_PASSWORD (used only to create and drop the scratch database)." >&2
    exit 1
fi

if [ -z "$dump_file" ]; then
    dump_file=$(ls -t "$project_root"/storage/backups/delibee-*.dump 2>/dev/null | head -n 1) || true
fi

if [ -z "$dump_file" ] || [ ! -f "$dump_file" ]; then
    echo "No backup dump found. Pass --dump-file or create one with backup-database.sh." >&2
    exit 1
fi

resolve_bin() {
    local name="$1"
    if [ -n "$pg_bin" ]; then
        printf '%s/%s' "${pg_bin%/}" "$name"
    else
        command -v "$name" || true
    fi
}

createdb_bin=$(resolve_bin createdb)
pg_restore_bin=$(resolve_bin pg_restore)
psql_bin=$(resolve_bin psql)
dropdb_bin=$(resolve_bin dropdb)

for binary in "$createdb_bin" "$pg_restore_bin" "$psql_bin" "$dropdb_bin"; do
    if [ -z "$binary" ] || [ ! -x "$binary" ]; then
        echo "Command not found: $binary. Pass --pg-bin or add PostgreSQL bin to PATH." >&2
        exit 1
    fi
done

row_tables=(users businesses categories products product_images cart_items orders order_items order_status_events reviews points_ledger rank_snapshots conversations messages)
row_query=""
for table in "${row_tables[@]}"; do
    [ -n "$row_query" ] && row_query+=" union all "
    row_query+="select '$table', count(*) from $table"
done
row_query+=" order by 1"

created=0
cleanup() {
    if [ "$created" = 1 ]; then
        PGPASSWORD="$admin_password" "$dropdb_bin" -h "$db_HOST" -p "$db_PORT" -U "$admin_user" --if-exists "$scratch_db" >/dev/null 2>&1 || true
    fi
}
trap cleanup EXIT

PGPASSWORD="$admin_password" "$createdb_bin" -h "$db_HOST" -p "$db_PORT" -U "$admin_user" -O "$db_USERNAME" "$scratch_db"
created=1

PGPASSWORD="$db_PASSWORD" "$pg_restore_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$scratch_db" -e "$dump_file"

live_tables=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$db_DATABASE" -Atc "select count(*) from information_schema.tables where table_schema='public'")
restored_tables=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$scratch_db" -Atc "select count(*) from information_schema.tables where table_schema='public'")

live_migrations=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$db_DATABASE" -Atc "select count(*) from migrations")
restored_migrations=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$scratch_db" -Atc "select count(*) from migrations")

live_rows=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$db_DATABASE" -Atc "$row_query")
restored_rows=$(PGPASSWORD="$db_PASSWORD" "$psql_bin" -h "$db_HOST" -p "$db_PORT" -U "$db_USERNAME" -d "$scratch_db" -Atc "$row_query")

if [ "$live_tables" != "$restored_tables" ]; then
    echo "Table counts differ: live=$live_tables restored=$restored_tables." >&2
    exit 1
fi

if [ "$live_migrations" != "$restored_migrations" ]; then
    echo "Migration counts differ: live=$live_migrations restored=$restored_migrations." >&2
    exit 1
fi

if [ "$live_rows" != "$restored_rows" ]; then
    echo "Row counts differ between the live and restored databases." >&2
    exit 1
fi

echo "Restore drill passed: $restored_tables tables, $restored_migrations migrations, all compared row counts matched."
echo "Dump: $dump_file"
