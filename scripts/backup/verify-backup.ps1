#Requires -Version 5.1
<#
.SYNOPSIS
    Restore a deliBEE backup into a scratch database and verify it.

.DESCRIPTION
    Picks the latest dump in storage\backups (or -DumpFile), creates a scratch
    database owned by the application role, restores the dump with
    pg_restore -e, compares table, migration and key row counts with the live
    database, then drops the scratch database.

    The admin password is only used to create and drop the scratch database.
    Pass it with -AdminPassword or set PG_ADMIN_PASSWORD.

.EXAMPLE
    $env:PG_ADMIN_PASSWORD = "your-postgres-password"
    powershell -ExecutionPolicy Bypass -File scripts\backup\verify-backup.ps1

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts\backup\verify-backup.ps1 `
      -DumpFile storage\backups\delibee-20261112-183737.dump
#>
[CmdletBinding()]
param(
    [string]$DumpFile,
    [string]$ScratchDatabase = "delibee_restore_check",
    [string]$AdminUser = "postgres",
    [string]$AdminPassword = $env:PG_ADMIN_PASSWORD,
    [string]$PgBin = "C:\Program Files\PostgreSQL\18\bin"
)

$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)
$envFile = Join-Path $projectRoot ".env"
$backupDir = Join-Path $projectRoot "storage\backups"

if (-not (Test-Path $envFile)) {
    throw "No .env file found at $envFile"
}

$config = @{}
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^\s*(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)\s*=\s*(.*)\s*$') {
        $config[$matches[1]] = $matches[2].Trim().Trim('"').Trim("'")
    }
}

foreach ($key in @("DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD")) {
    if (-not $config.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($config[$key])) {
        throw "$key is missing from $envFile"
    }
}

if ($ScratchDatabase -eq $config["DB_DATABASE"]) {
    throw "The scratch database must differ from the live database."
}

if ([string]::IsNullOrWhiteSpace($AdminPassword)) {
    throw "Pass -AdminPassword or set PG_ADMIN_PASSWORD (used only to create and drop the scratch database)."
}

if (-not $DumpFile) {
    $DumpFile = Get-ChildItem -Path $backupDir -Filter "delibee-*.dump" |
        Sort-Object LastWriteTime -Descending |
        Select-Object -First 1 -ExpandProperty FullName
}

if (-not $DumpFile -or -not (Test-Path $DumpFile)) {
    throw "No backup dump found. Pass -DumpFile or create one with backup-database.ps1."
}

foreach ($binary in @("createdb.exe", "pg_restore.exe", "psql.exe", "dropdb.exe")) {
    if (-not (Test-Path (Join-Path $PgBin $binary))) {
        throw "$binary not found in '$PgBin'. Pass -PgBin <path to PostgreSQL bin>."
    }
}

$hostName = $config["DB_HOST"]
$port = $config["DB_PORT"]
$liveDatabase = $config["DB_DATABASE"]
$appUser = $config["DB_USERNAME"]
$appPassword = $config["DB_PASSWORD"]

$rowTables = @(
    "users", "businesses", "categories", "products", "product_images",
    "cart_items", "orders", "order_items", "order_status_events", "reviews",
    "points_ledger", "rank_snapshots", "conversations", "messages"
)

$rowQuery = ($rowTables | ForEach-Object { "select '$($_)', count(*) from $_" }) -join " union all "
$rowQuery = "$rowQuery order by 1"

$previousPassword = $env:PGPASSWORD
$created = $false

try {
    $env:PGPASSWORD = $AdminPassword
    & (Join-Path $PgBin "createdb.exe") -h $hostName -p $port -U $AdminUser -O $appUser $ScratchDatabase
    if ($LASTEXITCODE -ne 0) {
        throw "createdb failed with exit code $LASTEXITCODE."
    }
    $created = $true

    $env:PGPASSWORD = $appPassword
    & (Join-Path $PgBin "pg_restore.exe") -h $hostName -p $port -U $appUser -d $ScratchDatabase -e $DumpFile
    if ($LASTEXITCODE -ne 0) {
        throw "pg_restore failed with exit code $LASTEXITCODE."
    }

    $psql = Join-Path $PgBin "psql.exe"

    $liveTables = & $psql -h $hostName -p $port -U $appUser -d $liveDatabase -Atc "select count(*) from information_schema.tables where table_schema='public'"
    $restoredTables = & $psql -h $hostName -p $port -U $appUser -d $ScratchDatabase -Atc "select count(*) from information_schema.tables where table_schema='public'"

    $liveMigrations = & $psql -h $hostName -p $port -U $appUser -d $liveDatabase -Atc "select count(*) from migrations"
    $restoredMigrations = & $psql -h $hostName -p $port -U $appUser -d $ScratchDatabase -Atc "select count(*) from migrations"

    $liveRows = & $psql -h $hostName -p $port -U $appUser -d $liveDatabase -Atc $rowQuery
    $restoredRows = & $psql -h $hostName -p $port -U $appUser -d $ScratchDatabase -Atc $rowQuery

    if ($LASTEXITCODE -ne 0) {
        throw "psql could not read one of the databases."
    }

    if (($liveTables -join "`n") -ne ($restoredTables -join "`n")) {
        throw "Table counts differ: live=$liveTables restored=$restoredTables."
    }

    if (($liveMigrations -join "`n") -ne ($restoredMigrations -join "`n")) {
        throw "Migration counts differ: live=$liveMigrations restored=$restoredMigrations."
    }

    if (($liveRows -join "`n") -ne ($restoredRows -join "`n")) {
        throw "Row counts differ between the live and restored databases."
    }

    Write-Host "Restore drill passed: $restoredTables tables, $restoredMigrations migrations, all compared row counts matched."
    Write-Host "Dump: $DumpFile"
}
finally {
    if ($created) {
        $env:PGPASSWORD = $AdminPassword
        & (Join-Path $PgBin "dropdb.exe") -h $hostName -p $port -U $AdminUser --if-exists $ScratchDatabase | Out-Null
    }

    $env:PGPASSWORD = $previousPassword
}
